<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserSpaceController extends AbstractController
{
    #[Route('/user/space', name: 'user_space')]
    public function index(): Response
    {
        return $this->render('user_space/user_space.html.twig', [
            'controller_name' => 'UserSpaceController',
        ]);
    }
}
