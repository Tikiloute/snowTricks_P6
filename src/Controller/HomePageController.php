<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(TrickRepository $trickRepository): Response
    {
        // parle_url scinde l'url avec ses différentes parties (PHP_URL_QUERY recupère que le query)
        // $parse = parse_url('https://www.youtube.com/watch?v=tOyBnioHnRA&t=200', PHP_URL_QUERY);
        // $result = [];
        // parse_str($parse, $result);
        // dd($result);
        $tricks = $trickRepository->getTrickHomePage();
        return $this->render('home_page/homepage.html.twig', [
            "tricks" => $tricks,
        ]);
    }
}
