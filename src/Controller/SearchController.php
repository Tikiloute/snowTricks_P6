<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function index(
        Request $request,
        TrickRepository $trickRepository,
    ): Response
    {
        $demand = $request->request->get("search");
        $tricks = $trickRepository->findBySearch($demand);
        return $this->render('search/index.html.twig', [
            "tricks" => $tricks,
        ]);
    }
}
