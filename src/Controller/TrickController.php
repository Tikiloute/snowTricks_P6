<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\Trick;
use App\Form\CommentaryType;
use App\Repository\CategoryRepository;
use App\Repository\CommentaryRepository;
use App\Repository\TrickRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TrickController extends AbstractController
{
    #[Route('/tricks', name: 'tricks')]
    public function allTricks(
        TrickRepository $trickRepository,
        Request $request,
        CategoryRepository $categoryRepository
    ): Response {
        //faire une const !
        $limit = 3;

        $page = max($request->query->getInt("page", 1), 1);
        $filters = $request->query->get("category");

        if ($filters === "") {
            $filters = null;
        }

        $countTricks = $trickRepository->getTotalTricks($filters);
        $tricks = $trickRepository->getPaginateTricks($page, $limit, $filters);

        if (ceil($countTricks / $limit) <= 0) {
            $numberOfPages = 1;
        } else {
            $numberOfPages = ceil($countTricks / $limit);
        }

        $categories = $categoryRepository->findAll();

        //ici on traite l'affiche pour la partie ajax
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->renderView('trick/content_tricks.html.twig', [
                    "page" => $page,
                    "tricks" => $tricks,
                    "countTricks" => $countTricks,
                    "numberOfPages" => $numberOfPages,
                    "limit" => $limit,
                    "categories" => $categories,
                    "filters" => $filters
                ])
            ]);
        }

        return $this->render('trick/all_tricks.html.twig', [
            "page" => $page,
            "tricks" => $tricks,
            "countTricks" => $countTricks,
            "numberOfPages" => $numberOfPages,
            "limit" => $limit,
            "categories" => $categories,
            "filters" => $filters
        ]);
    }

    #[Route('/trick/{id}', name: 'trick')]
    public function oneTrick(
        Trick $trick,
        TrickRepository $trickRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        CommentaryRepository $commentaryRepository
    ): Response {
        $commentary = new Commentary();
        $form = $this->createForm(CommentaryType::class, $commentary);
        $id = $request->get('id');
        $trick = $trickRepository->find($id);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $date = new \DateTime();
            $new  = $date->format('d-m-Y H:i');
            $commentary->setAuthor($this->getUser('id'));
            $commentary->setDate($new);
            $commentary->setTrick($trick);
            $entityManagerInterface->persist($commentary);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté');
        }

        //ici on vérifie la personne qui a écrit le commentaire et celle 
        //qui veut le supprimer sont les mêmes
        $get = $request->query->get('commentary');

        if (isset($get)) {
            $comm = $commentaryRepository->find($get);
        }
        if (isset($get, $comm) && $this->getUser('id') === $comm->getAuthor()) {
            $entityManagerInterface->remove($comm);
            $entityManagerInterface->flush();
            $this->addFlash('warning', 'Votre commentaire a bien été supprimé');
            return $this->redirectToRoute('trick', ['id' => $trick->getId()]);
        }

        return $this->render('trick/one_trick.html.twig', [
            "trick" => $trick,
            "form" => $form->createView()
        ]);
    }
}
