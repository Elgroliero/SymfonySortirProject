<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme', name: 'app_theme')]
    public function changeTheme(Request $request): Response
    {
        $selectedTheme = $request->request->get('selected_theme');

        $session = $request->getSession();
        $session->set('selected_theme', $selectedTheme);

        return $this->redirectToRoute('home_home');
    }
}
