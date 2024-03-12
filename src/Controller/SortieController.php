<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'home')]
class SortieController extends AbstractController
{


//    public function listSorties(EntityManagerInterface $entityManager, Request $request): Response
    #[Route(path: '', name: 'home', methods: ['GET'])]
    public function listSorties(EntityManagerInterface $entityManager): Response
    {

       // $total = $entityManager->getRepository(Sortie::class)->count(['etat' => 'ouvert']);

        $sorties = $entityManager->getRepository(Sortie::class)->findBy(
            ['etat' => 2]
        );

//        $queryBuilder = $entityManager->createQueryBuilder();
//        $sorties = $queryBuilder->select('s')
//            ->from('Sortie','s')
//            ->where($queryBuilder->expr()->isNotNull('s.name'))
//            ->getQuery()->getResult();
//
        return $this->render('sortie/index.html.twig', [
                'sorties' => $sorties
       ]);
    }

    #[Route('/detail/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function details(?Sortie $sortie): Response
    {
        if (!$sortie || !$sortie->getDateTimeStart('ouvert')) {
            throw $this->createNotFoundException('Cette sortie n\'existe pas/plus');
        }

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }
}
