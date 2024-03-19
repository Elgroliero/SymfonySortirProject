<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\ParticipantCSVType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Console\Style\SymfonyStyle;


class AdminController extends AbstractDashboardController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }




    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('SymfonySortirProject');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Participants', 'fas fa-users', Participant::class);
        yield MenuItem::linkToCrud('Sorties', 'fas fa-folder', Sortie::class);
        yield MenuItem::linkToCrud('Sites', 'fas fa-folder', Site::class);
        yield MenuItem::linkToCrud('Villes', 'fas fa-folder', Ville::class);

        yield MenuItem::linkToRoute('Accueil', 'fa fa-home', 'home_home');

    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/loadcsv', name: '_loadcsv')]
    public function loadCsv(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ParticipantCSVType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $csvFile = $formData['csv'];

            try {
                $handle = fopen($csvFile->getRealPath(), 'r');

                if ($handle !== false) {
                    // Skip the header row
                    fgetcsv($handle);


                    while (($record = fgetcsv($handle)) !== false) {
                        // Process each record
                        $participant = new Participant();
                        $participant->setSite($formData['site']);
                        $participant->setEmail($record[0]);
                        $password = "motdepasse";
                        $hashedPassword = $passwordHasher->hashPassword($participant, $password);
                        $participant->setPassword($hashedPassword);
                        $participant->setFirstName($record[3]);
                        $participant->setLastName($record[2]);
                        $participant->setActive($record[4]);
                        $participant->setUsername($record[5]);


                        // Persist each record
                        $entityManager->persist($participant);
                    }

                    fclose($handle);
                    //Puis flush
                    $entityManager->flush();
                    $this->addFlash('success', 'Les participants ont bien été importés.');
                    return $this->redirectToRoute('group_list');
                } else {
                    throw new \Exception('Le fichier CSV est invalide.');
                }
            } catch (\Exception $e) {
                return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
        return $this->render('admin/loadcsv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}


