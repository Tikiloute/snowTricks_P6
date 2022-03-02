<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\CreateTrickType;
use App\Form\ModifyTrickType;
use App\Form\ModifyPasswordType;
use App\Repository\UserRepository;
use App\Form\ModifyInformationsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSpaceController extends AbstractController
{
    #[Route('/user/space', name: 'user_space')]
    public function index(): Response
    {
        return $this->render('user_space/user_space.html.twig', [
            'controller_name' => 'UserSpaceController',
        ]);
    }

    #[Route('/user/modifyInformations', name: 'user_modify_informations')]
    public function modifyUserInformations(
        Request $request, 
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface
        ): Response
    {
        $userConnected = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(["email" => $userConnected]);
        $form = $this->createForm(ModifyInformationsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $this->addFlash('successInformationsModify', 'Les modifications concernant vos informations personnelles
            ont bien été effectuées');
            return $this->redirectToRoute('user_space');
        }
        
        return $this->render('user_space/user_modify_informations.html.twig', [
            "form" => $form->createView(),
            "user" => $user,
        ]);
    }

    #[Route('/user/createTrick', name: 'user_create_trick')]
    public function createTrick(
        Request $request, 
        EntityManagerInterface $entityManagerInterface,
    ): Response
    {
        $trick = new Trick;
        $form = $this->createForm(CreateTrickType::class, $trick);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $category = $request->get("category");

            //on récupère les images transmises
            $images = $form->get('images')->getData();

            //on boucle sur le tableau d'images
            foreach ($images as $image) {
                //on défini un nouveau nom pour les images
                $fichier = md5(uniqid()).'.'.$image->guessExtension();

                //on envoie le fichier dans le dossier upload
                $image->move($this->getParameter('images_directory'), $fichier);

                //stockage de l'image dans la bdd
                $img = new Image();
                $img->setPath($fichier);
                $trick->addImage($img);
            };
            $trick->setCategory($category);
            $trick->setAuthor($this->getUser());
            $entityManagerInterface->persist($trick);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Le trick à bien été crée !');
            return $this->redirectToRoute('trick', ['id' => $trick->getId()]);

        } 

        return $this->render('user_space/user_create_trick.html.twig', [
           "form" => $form->createView()
        ]);
    }

    #[Route('/user/modifyTrick/{id}', name: 'user_modify_trick')]
    public function modifyTrick(
        Request $request,
        Trick $trick,
        EntityManagerInterface $entityManagerInterface
    ): Response
    {
        $form = $this->createForm(ModifyTrickType::class, $trick);
        $images = $trick->getImages();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $category = $request->get("category");

            //on récupère les images transmises
            $images = $form->get('images')->getData();

            //on boucle sur le tableau d'images
            foreach ($images as $image) {
                //on défini un nouveau nom pour les images
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                //on envoie le fichier dans le dossier upload
                $image->move($this->getParameter('images_directory'), $fichier);

                //stockage de l'image dans la bdd
                $img = new Image();
                $img->setPath($fichier);
                $trick->addImage($img);
            };
            //ici on vérifie si le bouton est 'on' si oui on supprime l'image
            foreach ($trick->getImages() as $image){
                $id = $image->getId();
                if(isset($id) && $request->request->get($id) === "on"){
                    $entityManagerInterface->remove($image);
                    $entityManagerInterface->flush();
                }  
            }
            
            $trick->setCategory($category);
            $trick->setAuthor($this->getUser());
            $entityManagerInterface->persist($trick);
            $entityManagerInterface->flush();
            $this->addFlash('success', 'Le trick à bien été crée !');
            return $this->redirectToRoute('user_modify_trick', ['id' => $trick->getId()]);
        } 

        return $this->render('user_space/user_modify_trick.html.twig', [
            "form" => $form->createView(),
            "trick" => $trick
         ]);
    }

}
