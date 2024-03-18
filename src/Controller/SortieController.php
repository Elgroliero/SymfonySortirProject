<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\DeleteSortieType;
use App\Form\LieuType;
use App\Form\SortieFiltreType;
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
    public function listSorties(EtatRepository $etatRepository,EntityManagerInterface $entityManager,SortieRepository $sortieRepository, SortieRepository $sortieRepo, Request $request): Response
    {

       // $total = $entityManager->getRepository(Sortie::class)->count(['etat' => 'ouvert'])

        $formFilter = $this->createForm(SortieFiltreType::class);
        $formFilter->handleRequest($request);
        $userID = $this->getUser()->getId();
        $dateNow = new \DateTime('now');

        //Validation du formulaire de recherche
        if($formFilter->isSubmitted() && $formFilter->isValid()){
            $data = $formFilter->getData();
            $sorties = $sortieRepo->findSortiesbyFilter($data, $userID);
        }else{
           $sorties = $sortieRepo->findSortiesByFilter(null, $userID);
        }
        $sortieRepository->updateSortieState($sorties,$entityManager,$etatRepository);
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'formFilter' => $formFilter,
            'dateNow' => $dateNow
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
    #[Route('/create', name: '_create',methods: ['GET','POST'])]
    public function create(EtatRepository $er,EntityManagerInterface $em,Request $request):response{

        $lieu = new Lieu();

        $formLieu =$this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);

        if($formLieu->isSubmitted() && $formLieu->isValid()){
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Lieu ajouté avec succes');
            return $this->redirectToRoute('home_create');
        }

        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->addParticipant($this->getUser());
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($er->findOneBy(['id' => $request->get('etat')]));
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success','Sortie ajoutée avec succes');
            return $this->redirectToRoute('home_home');
        }

        return $this->render('sortie/create.html.twig',[
            'form' => $form,
            'formLieu' => $formLieu]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function deleteSortie(Sortie $sortie, EntityManagerInterface $em,EtatRepository $er,Request $request) : Response{

        $form = $this->createForm(DeleteSortieType::class,$sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($sortie->getOrganisateur() != $this->getUser()) {
                throw $this->createNotFoundException('Vous n\'avez pas le droit de supprimer cette sortie');
            }
            $sortie->setEtat($er->findOneBy(['id' => 6]));
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('home_home');
        }
        return $this->render('sortie/delete.html.twig',[
            'form' => $form,
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

    #[Route('/update/{id}', name: '_update', requirements: ['id' => '\d+'])]
    public function updateSortie(Sortie $sortie,Request $request) : Response{
    $form = $this->createForm(SortieType::class, $sortie);
    $form->handleRequest($request);

        return $this->render('sortie/update.html.twig',[
            'form' => $form,
            'sortie' => $sortie
        ]);
    }

    #[Route('/publish/{id}', name: '_publish', requirements: ['id' => '\d+'])]
    public function publishSortie(Sortie $sortie,EntityManagerInterface $em,SortieRepository $sortieRepository){
         $sortieRepository->publish($sortie,$em);
         return $this->redirectToRoute('home_home');
    }

}
