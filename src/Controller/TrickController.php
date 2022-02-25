<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TrickController extends AbstractController
{
    #[Route('/tricks', name: 'tricks')]
    public function allTricks(TrickRepository $trickRepository, Request $request): Response
    {
        $limit = 4;
        $page = (int)$request->get("page", 1);
        $countTricks = $trickRepository->getTotalTricks();
        $tricks = $trickRepository->getPaginateTricks($page, $limit);
        $numberOfPages = ceil($countTricks/$limit);

        return $this->render('trick/all_tricks.html.twig', [
            "page" => $page,
            "tricks" => $tricks,
            "countTricks" => $countTricks,
            "numberOfPages" => $numberOfPages,
            "limit" => $limit
        ]);
    }

    #[Route('/trick/{id}', name: 'trick')]
    public function oneTrick(Trick $trick, TrickRepository $trickRepository, Request $request): Response
    {
        $id = $request->get('id');
        $trick = $trickRepository->find($id);
        return $this->render('trick/one_trick.html.twig', [
            "trick" => $trick,
        ]);
    }
}
