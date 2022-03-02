<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\Trick;
use App\Form\CommentaryType;
use App\Repository\TrickRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
        $page = max($request->query->getInt("page", 1), 1);
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
    public function oneTrick(
        Trick $trick,
        TrickRepository $trickRepository,
        Request $request,
        EntityManagerInterface $entityManagerInterface,
    ): Response
    {
        $commentary = new Commentary();
        $form = $this->createForm(CommentaryType::class, $commentary);
        $id = $request->get('id');
        $trick = $trickRepository->find($id);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $date = new \DateTime();
            $new  = $date->format('d-m-Y H:i');
            $commentary->setAuthor($this->getUser('id'));
            $commentary->setDate($new);
            $commentary->setTrick($trick);
            $entityManagerInterface->persist($commentary);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté');
        
        }

        return $this->render('trick/one_trick.html.twig', [
            "trick" => $trick,
            "form" => $form->createView()
        ]);
    }

}
