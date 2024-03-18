<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $participant = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        //Si le formulaire est soumis et validé, on enregistre les modifications
        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('image')->getData() instanceof UploadedFile) {
                $image = $form->get('image')->getData();
                $fileName = $slugger->slug($participant->getUsername()) . ' - ' . uniqid() . ' . ' . $image->guessExtension();
                $image->move(
                    $this->getParameter('picture_dir'),
                    $fileName
                );
                $participant->setImage($fileName);
            }

            // encode the plain password
            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );

            //rôle User par défaut pour les utilisateurs
            $participant->setRoles(['ROLE_USER']);
            $participant->setActive(1);
            $entityManager->persist($participant);
            $entityManager->flush();

            //Message flash de succes d'inscription
            $this->addFlash('success', 'Inscription réussie');
            // Redirection vers la page de login
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'participant' => $participant,
            'registrationForm' => $form,
        ]);
    }
}
