<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')] // Permet de dire que l'utilisateur doit être connecté
#[Route('/', name: 'profile')]
class ProfileController extends AbstractController
{

    #[Route('profile', name: '_user')]
    public function profile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $participant = $this->getUser();
        // Formulaire d'édition du profil (récupère l'id avec l'objet participant)
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        //Si le formulaire est soumis et validé, on enregistre les modifications
        if($form->isSubmitted() && $form->isValid()) {

            //validation de la photo de profil
            if($form->get('image')->getData() instanceof UploadedFile) {
                $image = $form->get('image')->getData();
                $fileName = $slugger->slug($participant->getUsername()) . ' - ' . uniqid() . ' . ' . $image->guessExtension();
                $image->move(
                    $this->getParameter('picture_dir'),
                    $fileName
                );
                if (!empty($participant->getImage())) {
                    $picturePath = $this->getParameter('picture_dir') . '/' . $participant->getImage();
                    if(file_exists($picturePath)) {
                        unlink($picturePath);
                    }
                }
                $participant->setImage($fileName);
            }

            // Cryptage du mot de passe ou nouveau mot de passe
            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );

            //Persite des nouvelles données et envoie a la base de donnée
            $entityManager->persist($participant);
            $entityManager->flush();

            // Message flash de succes
            $this->addFlash('success', 'Votre profil a bien été mis à jour');
            return $this->redirectToRoute('home_home');
        }

        return $this->render('profile/edit-profile.html.twig', [
            'participant' => $participant,
            'profile_form' => $form->createView()
        ]);
    }


    #[Route('user/{id}', name: '_users', requirements: ['id' => '[0-9]\d*'])]
    public function usersProfile(EntityManagerInterface $entityManager, int $id): Response
    {

        $participant = $entityManager->getRepository(Participant::class)->getParticipantById($id);

        //Si aucun participant n'a été trouve, lance une exception
        if(!$participant) {
            throw $this->createNotFoundException('Le participant n\'a pas été trouvé');
        }
        return $this->render('profile/users-profile.html.twig', [
            'participant' => $participant
        ]);
    }

}