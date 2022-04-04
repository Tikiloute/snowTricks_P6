<?php

namespace App\Controller;

use App\Form\ChangeRoleType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdminSpaceController extends AbstractController
{

    #[Route('/admin/space', name: 'admin_space')]
    public function index(): Response
    {
        return $this->render('admin_space/admin_space.html.twig', []);
    }

    #[Route('/admin/space/changeRole', name: 'admin_space_change_role')]
    public function changeRole(
        EntityManagerInterface $entityManagerInterface,
        Request $request,
        UserRepository $userRepository
    ): Response {

        if ($request->get("change_role") !== null) {
            $email = $request->get("change_role");
            $user = $userRepository->findOneBy(["email" => $email["email"]]);
        }

        $form = $this->createForm(ChangeRoleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user !== null) {
                $role = $request->get("category");
                if ($role === 'ROLE_USER' || $role === 'ROLE_ADMIN') {
                    $user->setRoles([$role]);
                    $entityManagerInterface->persist($user);
                    $entityManagerInterface->flush();

                    $this->addFlash('success', 'Le changement de rôle a bien été effectué');
                    return $this->redirectToRoute('admin_space_change_role');
                } else {
                    $this->addFlash('danger', 'Le rôle sélectionné n\'existe pas');
                    return $this->redirectToRoute('admin_space_change_role');
                }
            } else {
                $this->addFlash('danger', 'L\'utilisateur n\'existe pas');
                return $this->redirectToRoute('admin_space_change_role');
            }
        }

        return $this->render('admin_space/change_role.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    #[Route('/admin/space/deleteUser', name: 'admin_space_delete_user')]
    public function deleteUser(
        EntityManagerInterface $entityManagerInterface,
        Request $request,
        UserRepository $userRepository
    ): Response {

        $form = $this->createForm(ChangeRoleType::class);
        $form->handleRequest($request);
        $user = null;
        $email = $request->get("change_role");

        if ($form->isSubmitted() && $form->isValid() ) {

            $user = $userRepository->findOneBy(["email" => $email["email"]]);

            if ($user != null) {

                $entityManagerInterface->remove($user);
                $entityManagerInterface->flush();
    
                $this->addFlash('success', 'L\'utilisateur a bien été supprimé');
                return $this->redirectToRoute('admin_space_delete_user');

            } else {
                $this->addFlash('danger', 'L\'utilisateur n\'existe pas');
                return $this->redirectToRoute('admin_space_delete_user');
            }
           
        } 

        return $this->render('admin_space/delete_user.html.twig', [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }
}
