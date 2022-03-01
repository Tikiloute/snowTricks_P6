<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdminSpaceController extends AbstractController
{
    
    #[Route('/admin/space', name: 'admin_space')]
    public function index(): Response
    {
        return $this->render('admin_space/admin_space.html.twig', [
            
        ]);
    }

    #[Route('/admin/modifyTrick', name: 'modify_trick')]
    public function modifyTrick(TrickRepository $trickRepository, Request $request): Response
    {
        $tricks = $trickRepository->findAll();
        return $this->render('admin_space/modify_trick.html.twig', [
            "tricks" => $tricks,
        ]);
    }

}
