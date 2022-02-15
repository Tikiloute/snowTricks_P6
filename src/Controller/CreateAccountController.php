<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateAccountType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\From;
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
        MailerInterface $mailer
    ): Response
    {

        $user = new User;
        $form = $this->createForm(CreateAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            //dd($user->getEmail());
            $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPassword()
            );
            $email = (new Email())
                ->from('bruno.etcheverry@celemma.eu')
                ->to($user->getEmail())
                ->subject('Validation du compte')
                ->text('Veuillez cliquer sur le lien pour valider votre compte')
                ->html('<p> Mettre du html et twig ici pour un email </>');
            $mailer->send($email);
            
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            return $this->redirectToRoute('app_login');
        }
        return $this->render('create_account/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
