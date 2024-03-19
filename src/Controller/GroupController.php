<?php

namespace App\Controller;

use App\Entity\Groups;
use App\Form\GroupType;
use App\Repository\GroupsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groups', name: 'group')]
class GroupController extends AbstractController
{
    #[Route('', name: '_list')]
    public function index(GroupsRepository $gr, Request $request): Response
    {

        $user = $this->getUser()
            ->getId();

        $groups = $gr->findByUser($user);

        return $this->render('groups/listgroups.html.twig', [
            'groups' => $groups
        ]);

    }

    #[Route('/create', name: '_create')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $group = new Groups();
        $formGroup = $this->createForm(GroupType::class, $group);

        $formGroup->handleRequest($request);

        if($formGroup->isSubmitted() && $formGroup->isValid()){
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Le groupe a bien été crée');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('groups/creategroups.html.twig', [
            'formGroup' => $formGroup->createView()
        ]);
    }

    #[Route('/{id}', name: '_show', requirements: ['id' => '[0-9]\d*'])]
    public function show(Groups $group): Response
    {

        return $this->render('groups/showgroup.html.twig', [
            'group' => $group
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', requirements: ['id' => '[0-9]\d*'])]
    public function edit(Groups $group, EntityManagerInterface $entityManager, Request $request): Response
    {
        $formEdit = $this->createForm(GroupType::class, $group);
        $formEdit->handleRequest($request);

        if($formEdit->isSubmitted() && $formEdit->isValid()){
            $entityManager->persist($group);
            $entityManager->flush();

            $this->addFlash('success', 'Le groupe a bien été mis à jour');
            return $this->redirectToRoute('group_list');
        }

        return $this->render('groups/editgroup.html.twig', [
            'formEdit' => $formEdit->createView(),
            'group' => $group
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '[0-9]\d*'])]
    public function delete(Groups $group, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($group);
        $entityManager->flush();
        $this->addFlash('success', 'Le groupe a bien été supprimé');
        return $this->redirectToRoute('group_list');
    }
}