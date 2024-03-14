<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')] // Seul les utilisateurs authentifiés peuvent accéder à cette page
#[Route(name:'home')]
class SortieController extends AbstractController
{

//    public function listSorties(EntityManagerInterface $entityManager, Request $request): Response
    #[Route(path: '', name: '_home')]
    public function listSorties(EntityManagerInterface $entityManager, SiteRepository $site, Request $request): Response
    {

       // $total = $entityManager->getRepository(Sortie::class)->count(['etat' => 'ouvert'])

        $sites = $site->findAll();

        //Validation du formulaire de recherche par site
        if($_POST) {
            $site = $request->get('filtre-site');
            $nom = $request->get('filtre-nom');
            $date = $request->get('filtre-date');
            $date2 = $request->get('filtre-date2');
            $queryBuilder = $entityManager->getRepository(Sortie::class)->createQueryBuilder('s');
            if($site) {
                $queryBuilder
                    ->andWhere('s.siteOrga = :site')
                    ->setParameter('site', $site);
            }
            if($nom) {
                $queryBuilder
                    ->andWhere('s.name LIKE :nom')
                    ->setParameter('nom', '%'.$nom.'%');
            }
            if($date && $date2) {
                $queryBuilder
                    ->andWhere('s.dateTimeStart BETWEEN :date AND :date2')
                    ->setParameter('date', $date)
                    ->setParameter('date2', $date2);
            }
            $sorties = $queryBuilder->getQuery()->getResult();
        } else {
            $sorties = $entityManager->getRepository(Sortie::class)->findBy(
                ['etat' => 2]
            );
        }

        return $this->render('sortie/index.html.twig', compact('sorties', 'sites'));

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

            $sortie->setEtat($er->findOneBy(['id' => $request->get('etat')]));

            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('home_home');
        }
        return $this->render('sortie/create.html.twig',['form' => $form->createView()]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function deleteSortie(Sortie $sortie, EntityManagerInterface $em,EtatRepository $er) : Response{

        if ($_POST) {

            if ($sortie->getOrganisateur() != $this->getUser()) {
                throw $this->createNotFoundException('Vous n\'avez pas le droit de supprimer cette sortie');
            }
            $sortie->setEtat($er->findOneBy(['id' => 6]));
            $sortie->setMotif($_POST['motif']);
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('home_home');
        }
        return $this->render('sortie/delete.html.twig',[
           'sortie' => $sortie
        ]);
    }

    #[Route('/register/{id}', name: '_register', requirements: ['id' => '\d+'])]
    public function RegisterToSortie(Sortie $sortie, EntityManagerInterface $em,SortieRepository $sortieRepository) : Response{
        if($sortieRepository->subscribe($sortie,$em,$this->getUser())) {
            $this->addFlash('success','Inscription reussie');
        }else{
            $this->addFlash('danger','Inscription echouée');
        }
        return $this->redirectToRoute('home_home');
    }
    #[Route('/unsub/{id}', name: '_unsub', requirements: ['id' => '\d+'])]
    public function unSubscribeFromSortie(Sortie $sortie, EntityManagerInterface $em,SortieRepository $sortieRepository) : Response{

        if($sortieRepository->unSubscribe($sortie,$em,$this->getUser())) {
            $this->addFlash('success','Inscription annulée');
        }else{
            $this->addFlash('danger','Annulation echouée');
        }
        return $this->redirectToRoute('home_home');
    }

}
