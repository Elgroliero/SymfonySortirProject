<?php

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ParticipantCrudController extends AbstractCrudController
{

    private UserPasswordHasherInterface $passwordEncoder;
    private SiteRepository $siteRepository;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, SiteRepository $siteRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->siteRepository = $siteRepository;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('username', 'Pseudo'),
            EmailField::new('email', 'Email'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            AssociationField::new('site')->setLabel('Site'),
//            ChoiceField::new('site', 'Site')->setChoices($this->getSiteChoices()),
            TextField::new('phoneNumber', 'Téléphone'),
            TextField::new('password', 'Mot de passe')->onlyOnForms(),
            ChoiceField::new('roles', 'Roles')->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])->allowMultipleChoices(),
            BooleanField::new('active', 'Active'),
            ];
    }

    public static function getEntityFqcn(): string
    {
        return Participant::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Participant) {
            $entityInstance->setPassword(
                $this->passwordEncoder->hashPassword($entityInstance, $entityInstance->getPassword())
            );
        }
        parent::persistEntity($entityManager, $entityInstance);

    }

    private function getSiteChoices(): array
    {
        $choices = [];
        $sites = $this->siteRepository->findAllSites();
        foreach ($sites as $site) {
            $choices[$site->getName()] = $site;
        }
        return $choices;
    }
}
