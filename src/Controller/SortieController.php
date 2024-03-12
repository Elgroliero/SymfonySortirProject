<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name:'home')]
class SortieController extends AbstractController
{

    #[Route('/create', name: '_create',methods: ['GET','POST'])]
    public function create(EtatRepository $er,EntityManagerInterface $em,Request $request):response{
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(!$form->get('lieu')->getData()) {
                if(!$form->get('lieu_name')->getData() || !$form->get('lieu_street')->getData() || !$form->get('lieu_lat')->getData() || !$form->get('lieu_long')->getData() || !$form->get('lieu_city')->getData()) {
                    return $this->render('sortie/create.html.twig',['form' => $form->createView()]);
                }
                $lieu = new Lieu();
                $lieu->setName($form->get('lieu_name')->getData());
                $lieu->setStreet($form->get('lieu_street')->getData());
                $lieu->setLatitude($form->get('lieu_lat')->getData());
                $lieu->setLongitude($form->get('lieu_long')->getData());
                $lieu->setVille($form->get('lieu_city')->getData());
                $sortie->setLieu($lieu);
                $em->persist($lieu);

            }
            $sortie->addParticipant($this->getUser());
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($er->findOneBy(['id' => 2]));

            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('home_home');
        }
        return $this->render('sortie/create.html.twig',['form' => $form->createView()]);
    }

//    public function listSorties(EntityManagerInterface $entityManager, Request $request): Response
    #[Route(path: '', name: '_home', methods: ['GET'])]
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
