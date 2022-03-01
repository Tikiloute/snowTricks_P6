<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mailer;
use App\Form\CreateAccountType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Form\ForgottenPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForgottenPasswordController extends AbstractController
{
    #[Route('/forgotten/password', name: 'forgotten_password')]
    public function index(
        Mailer $mailer,
        Request $request,
        UserRepository $userRepository,
    ): Response
    {

        $form = $this->createForm(ForgottenPasswordType::class); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $userMail = $request->get('forgotten_password')["email"];
            $user = $userRepository->findOneBy(['email' => $userMail]);
            $mailer->send($user->getEmail(), "Réinitialisation du mot de passe", "
            <h1> Réinitialisation du mot de passe </h1>
            <br>
            <p>
            Souhaitez vous réinitialiser votre mot de passe ? 
            Si oui, veuillez cliquer sur le lien ci-dessous : 
            </p>
            <a href='http://127.0.0.1:8000/reset/password?user=".$user->getId()."&token=".$user->getToken()."'>Lien vers la validation du compte</a>
            <br>
            <p>Dans le cas où vous n'avez pas fait cette demande, veuillez ignorer cet e-mail</p>
            ");

            $this->addFlash('emailSent', 'Un email vient de vous être envoyé afin de réinitialiser votre
            mot de passe');

        }
        return $this->render('forgotten_password/forgotten_password.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/reset/password', name: 'reset_password')]
    public function resetPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
    ): Response
    {
        $userId = $request->get("user");
        $userToken = $request->get("token");
        $user = $userRepository->findOneBy(['id' => $userId]);
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if ($user->getToken() === $userToken){
                //ici on recupère le nouveau mot de passe et on le hash
                $userPassword = $request->get('reset_password')["password"]['first'];
                $newUserPassword = password_hash($userPassword, PASSWORD_DEFAULT );
                //on crée un nouveau token et on le hash
                $token = random_bytes(20);
                $tokenHash = password_hash($token, PASSWORD_DEFAULT );
                //on modifie les données du user (password et token pour éviter de pouvoir réinitialiser
                // avec le même email reçu plusieurs fois
                $user->setPassword($newUserPassword);
                $user->setToken($tokenHash);
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                return $this->redirectToRoute('app_login');
                $this->addFlash('successResetPassword', 'Mot de passe modifié avec succès');


            }else {
                $this->addFlash('danger', 'Le lien de l\'email n\'est plus valide, veuillez refaire une demande de
                changement de mot de passe');
            }
            
           
        }

        return $this->render('forgotten_password/reset_password.html.twig', [
            "form" => $form->createView(),
        ]);
    }
}
