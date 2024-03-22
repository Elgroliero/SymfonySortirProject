<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\DeleteSortieType;
use App\Form\LieuType;
use App\Form\SortieFiltreType;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')] // Seul les utilisateurs authentifiés peuvent accéder à cette page
#[Route(name:'home')]
class SortieController extends AbstractController
{

//    public function listSorties(EntityManagerInterface $entityManager, Request $request): Response
    #[Route(path: '', name: '_home')]
    public function listSorties(EtatRepository $etatRepository,EntityManagerInterface $entityManager,SortieRepository $sortieRepository, SortieRepository $sortieRepo, Request $request): Response
    {
        $formFilter = $this->createForm(SortieFiltreType::class);
        $formFilter->handleRequest($request);
        $userID = $this->getUser();
        $dateNow = new \DateTime('now');
        //Validation du formulaire de recherche
        $session = $request->getSession();
        if($request->query->get('p',1)<0){
            $page = 1;
        }else{
            $page =$request->query->get('p',1);
        }
        if($formFilter->isSubmitted() && $formFilter->isValid()){
            $data = $formFilter->getData();
            $sorties = $sortieRepo->findSortiesbyFilter($data, $userID,$page);
            $session->set('filters' ,$data);
        }else{
            if($session->get('filters')){
                $sorties = $sortieRepo->findSortiesbyFilter($session->get('filters'), $userID,$page);
            }else{
                $sorties = $sortieRepo->findSortiesByFilter(null, $userID,$page);
            }
        }
        $sortieRepository->updateSortieState($sorties[0],$entityManager,$etatRepository);
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties[0],
            'formFilter' => $formFilter,
            'dateNow' => $dateNow,
            'nbPages' => ceil($sorties[1]/6),
            'page' => $page
        ]);

    }

    #[Route('/detail/{id}', name: '_detail', requirements: ['id' => '[0-9]\d*'])]
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
    public function create(EtatRepository $er,EntityManagerInterface $em,Request $request, SluggerInterface $slugger):response{

        $lieu = new Lieu();

        $formLieu =$this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);

        if($formLieu->isSubmitted() && $formLieu->isValid()){
            $em->persist($lieu);
            $em->flush();
            $this->addFlash('success', 'Lieu ajouté avec succes');
            return $this->redirectToRoute('home_create');
        }

        $ville = new Ville();

        $formVille = $this->createForm(VilleType::class, $ville);
        $formVille->handleRequest($request);

        if($formVille->isSubmitted() && $formVille->isValid()){
            $em->persist($ville);
            $em->flush();
            $this->addFlash('success', 'Ville ajoutée avec succes');
            return $this->redirectToRoute('home_create');
        }

        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->addParticipant($this->getUser());
            $sortie->setOrganisateur($this->getUser());
            $sortie->setEtat($er->findOneBy(['id' => $request->get('etat')]));

            if ($form->get('picture')->getData() instanceof UploadedFile) {
                $picture = $form->get('picture')->getData();
                $fileName = $slugger->slug($sortie->getName()) . ' - ' . uniqid() . ' . ' . $picture->guessExtension();
                $picture->move(
                    $this->getParameter('image_dir'),
                    $fileName
                );
                if (!empty($sortie->getPicture())) {
                    $picturePath = $this->getParameter('image_dir') . '/' . $sortie->getPicture();
                    if (file_exists($picturePath)) {
                        unlink($picturePath);
                    }
                }
                $sortie->setPicture($fileName);
            }

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success','Sortie ajoutée avec succes');
            return $this->redirectToRoute('home_home');
        }

        return $this->render('sortie/create.html.twig',[
            'form' => $form,
            'formLieu' => $formLieu,
            'formVille' => $formVille
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function deleteSortie(Sortie $sortie, EntityManagerInterface $em,EtatRepository $er,Request $request) : Response{

        $form = $this->createForm(DeleteSortieType::class,$sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if($sortie->getOrganisateur()->getId() == $this->getUser()->getid() || $this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {


                if ($sortie->getEtat()->getName() == 'Ouverte' || $sortie->getEtat()->getName() == 'Clôturée' || $sortie->getEtat()->getName() == 'Créée') {
                    $sortie->setEtat($er->findOneBy(['id' => 6]));
                    $em->persist($sortie);
                    $em->flush();
                    $this->addFlash('success', 'Sortie supprimée avec succes');
                    return $this->redirectToRoute('home_home');
                } else {
                    $this->addFlash('danger', 'Impossible de supprimer cette sortie');
                    return $this->redirectToRoute('home_home');
                }
            }else{
                $this->addFlash('danger', 'Interdix');
            }
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
    public function updateSortie(Sortie $sortie,Request $request,EntityManagerInterface $em,EtatRepository $er) : Response{
    $form = $this->createForm(SortieType::class, $sortie);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()){
        $em->persist($sortie);
        $em->flush();
        $this->addFlash('success','Sortie publiée avec succes');
        return $this->redirectToRoute('home_update', ['id' => $sortie->getId()]);
    }
        return $this->render('sortie/update.html.twig',[
            'form' => $form,
            'sortie' => $sortie
        ]);
    }

    #[Route('/publish/{id}', name: '_publish', requirements: ['id' => '\d+'])]
    public function publishSortie(Sortie $sortie,EntityManagerInterface $em,SortieRepository $sortieRepository){
        if($sortie->getEtat()->getName() === 'Créée') {
            $sortieRepository->publish($sortie, $em);
            $this->addFlash('success', 'Sortie publiee avec succes');
        }else{
            $this->addFlash('danger', 'Impossible de publier cette sortie');
        }
        return $this->redirectToRoute('home_home');
    }

}
