<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'lieu')]
class LieuController extends AbstractController
{
    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function AjaxGetLieuById(Lieu $lieu): Response{
        $lieuArray = [
            'id' => $lieu->getId(),
            'name' => $lieu->getName(),
            'street' => $lieu->getStreet(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
            'ville' => $lieu->getVille()->getName(),
        ];
        return new JsonResponse($lieuArray, 200, ['Content-Type' => 'application/json']);
    }
}