<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CreateTrickType;
use App\Form\ModifyTrickType;
use App\Form\ModifyPasswordType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use App\Form\ModifyInformationsType;
use App\Repository\CommentaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSpaceController extends AbstractController
{
    #[Route('/user/space', name: 'user_space')]
    public function index(UserRepository $userRepository): Response
    {

        $userMail = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $userMail]);
        $count = count($user->getCommentaries());
        return $this->render('user_space/user_space.html.twig', [
            'controller_name' => 'UserSpaceController',
            'count' => $count,
            'user' => $user
        ]);
    }

    #[Route('/user/modifyTrick', name: 'modify_trick')]
    public function modifyTrickList(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findAll();
        return $this->render('admin_space/modify_trick.html.twig', [
            "tricks" => $tricks,
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

            //partie vidéo ici
            $videoForm = $form->get('video')->getData();
            $video = new Video();
            if (isset($videoForm) && !is_null($videoForm)){
                if (str_contains($videoForm, 'youtube')){
                    $urlExplode = explode('/watch?v=', $videoForm);
                    if (str_contains($urlExplode[1], "&")){
                        $explodeUrlExplode = explode('&', $urlExplode[1]);
                        $urlYoutube = "https://www.youtube.com/embed/".$explodeUrlExplode[0];
                        $video->setUrl($urlYoutube);
                        $trick->addVideo($video);
                    } else {
                        $urlYoutube = "https://www.youtube.com/embed/".$urlExplode[1];
                        $video->setUrl($urlYoutube);
                        $trick->addVideo($video);
                    }        
                } elseif (str_contains($videoForm, 'dailymotion')){
                    $urlExplode = explode('video/', $videoForm);
                    if (str_contains($urlExplode[1], "?")){
                        $explodeUrlExplode = explode('?', $urlExplode[1]);
                        $urlDailyMotion = "https://www.dailymotion.com/embed/video/".$explodeUrlExplode[0];
                        $video->setUrl($urlDailyMotion);
                        $trick->addVideo($video);
                    } else {
                        $urlDailyMotion = "https://www.dailymotion.com/embed/video/".$urlExplode[1];
                        $video->setUrl($urlDailyMotion);
                        $trick->addVideo($video);
                    }
                } else {
                    $this->addFlash('warning', 'La vidéo n\'a pas été ajoutée car ce n\'est pas 
                    une vidéo youtube/dailymotion ou que l\'url était érronée');
                }
            }
            
            //fin partie video

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

            //partie vidéo ici
            $formVideo = $form->get('video')->getData();
            $video = new Video();
            if (isset($formVideo) && !is_null($formVideo)){
                if (str_contains($formVideo, 'www.youtube')){
                    $parseUrl = parse_url($formVideo, PHP_URL_QUERY);
                    $result = [];
                    parse_str($parseUrl, $result);
                    $urlYoutube = "https://www.youtube.com/embed/".$result["v"];
                    $video->setUrl($urlYoutube);
                    $trick->addVideo($video);
                    
                } elseif (str_contains($formVideo, 'www.dailymotion')){
                    $parseUrl = parse_url($formVideo, PHP_URL_PATH);
                    $urlDailyMotion = "https://www.dailymotion.com/embed".$parseUrl;
                    $video->setUrl($urlDailyMotion);
                    $trick->addVideo($video);
        
                } else {
                    $this->addFlash('warning', 'La vidéo n\'a pas été ajoutée car ce n\'est pas 
                    une vidéo youtube/dailymotion ou que l\'url était érronée');
                }
            }
            
            foreach ($trick->getVideos() as $video){
                $id = $video->getId();
                if(isset($id) && $request->request->get($id) === "on"){
                    $entityManagerInterface->remove($video);
                    $entityManagerInterface->flush();
                }  
            }
            //fin partie video
            
            $trick->setCategory($category);
            $trick->setAuthor($this->getUser());
            $entityManagerInterface->persist($trick);
            $entityManagerInterface->flush();
            $this->addFlash('success', 'Le trick à bien été modifié !');
            return $this->redirectToRoute('user_modify_trick', ['id' => $trick->getId()]);
        } 

        //suppression d'un trick
        $get = $request->query->get('delete');

        if (isset($get) && $get = true){
            $entityManagerInterface->remove($trick);
            $entityManagerInterface->flush();
            $this->addFlash('warning', 'Votre trick a bien été supprimé');
            return $this->redirectToRoute('modify_trick');
            
        }

        return $this->render('user_space/user_modify_trick.html.twig', [
            "form" => $form->createView(),
            "trick" => $trick
         ]);
    }

    #[Route('/user/viewCommentary', name: 'user_view_commentary')]
    public function viewCommentary(
        UserRepository $userRepository, 
        EntityManagerInterface $entityManagerInterface,
        Request $request,
        CommentaryRepository $commentaryRepository
    ): Response
    {
        $userConnected = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(["email" => $userConnected]);

        $get = $request->query->get('commentary');

        if (isset($get)){
            $comm = $commentaryRepository->find($get);
        }
        if (isset($get, $comm) && $this->getUser('id') === $comm->getAuthor()){
            $entityManagerInterface->remove($comm);
            $entityManagerInterface->flush();
            $this->addFlash('warning', 'Votre commentaire a bien été supprimé');
            return $this->redirectToRoute('user_view_commentary');
            
        }

        return $this->render('user_space/user_view_commentaries.html.twig', [
            "user" => $user,
         ]);

    }

}
