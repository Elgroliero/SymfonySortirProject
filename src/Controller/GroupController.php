<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/groups', name: 'group')]
class GroupController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function index(): Response
    {
        return $this->render('groups/showgroups.html.twig');

    }
}