<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\EditProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'profile')]
class ProfileController extends AbstractController
{

    #[Route('profile/{id}', name: '_user', requirements: ['id' => '[0-9]\d*'])]
    public function profile(Participant $participant, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        // Formulaire d'édition du profil (récupère l'id avec l'objet participant)
        $form = $this->createForm(EditProfileType::class, $participant);
        $form->handleRequest($request);

        //Si le formulaire est soumis et validé, on enregistre les modifications
        if($form->isSubmitted() && $form->isValid()) {

            //TODO:faire le if de validation et d'enregistrement de la photo de profil

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
            //TODO: Redirection vers la page d'accueil
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