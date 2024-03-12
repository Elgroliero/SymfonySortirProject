<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie',name:'sortie_')]
class SortieController extends AbstractController
{

    #[Route('/create/{id}', name: 'create',methods: ['GET','POST'])]
    public function create(Participant $orga,EtatRepository $er,EntityManagerInterface $em,Request $request):response{
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = new Lieu();
            $lieu->setName($form->get('lieu_name')->getData());
            $lieu->setStreet($form->get('lieu_street')->getData());
            $lieu->setLatitude($form->get('lieu_lat')->getData());
            $lieu->setLongitude($form->get('lieu_long')->getData());
            $lieu->setVille($form->get('lieu_city')->getData());
            $sortie->setLieu($lieu);
            $sortie->setOrganisateur($orga);
            $sortie->setEtat($er->findOneBy(['id' => 1]));
            $em->persist($lieu);
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('app_zobi');
        }
        return $this->render('sortie/create.html.twig',['form' => $form->createView()]);
    }
}