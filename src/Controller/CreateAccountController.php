<?php

namespace App\Controller;

use eu;
use App\Entity\User;
use App\Form\CreateAccountType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAccountController extends AbstractController
{
    #[Route('/create/account', name: 'create_account')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManagerInterface,
        Mailer $mailer
    ): Response
    {
        $token = random_bytes(20);
        $tokenHash = password_hash($token, PASSWORD_DEFAULT );
        $user = new User;
        $form = $this->createForm(CreateAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
    
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setToken($tokenHash);
            $user->setIsValid(false);
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            $mailer->send($user->getEmail(), "Validation du compte", "
            <h1> Merci d'avoir crée votre compte sur le site SnowTricks </h1>
            <br>
            <p>
            Cependant votre travail n'est pas encore terminé, pour valider
            votre compte vous devez cliquer sur le lien ci dessous
            </p>
            <a href='http://127.0.0.1:8000/link?user=".$user->getId()."&token=".$user->getToken()."'>Lien vers la validation du compte</a>
            ");
            return $this->redirectToRoute('app_login');
        }
        return $this->render('create_account/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/link', name: 'valid_account')]
    public function validAccount(
        Request $request, 
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface
    )
    {
        if($request->get("token")){

        $userId = $request->get('user');
        $token = $request->get("token");
        $user = $userRepository->findOneBy(['id' => $userId]);
        $user->setIsValid("true");
        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();

        return $this->render('create_account/account_created.html.twig', [
           "token" => $token,
           "user" => $user
        ]);
        
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
