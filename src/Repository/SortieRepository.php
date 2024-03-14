<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function subscribe(Sortie $entity,EntityManagerInterface $entityManager, Participant $participant): bool{
        if(!($entity->getParticipants()->count()<$entity->getMaxInscriptionNb())|| $entity->getDateInscriptionLimit()<new \DateTime('now')){
            return false;
        }

        $entity->addParticipant($participant);
       $entityManager->persist($entity);
       $entityManager->flush();
       return true;
    }

    public function unSubscribe(Sortie $entity,EntityManagerInterface $entityManager, Participant $participant): bool{
        if(!(array_search($participant, $entity->getParticipants()->toArray()))||$entity->getDateTimeStart()<new \DateTime('now')){
            return false;
        }
       $entity->removeParticipant($participant);
       $entityManager->persist($entity);
       $entityManager->flush();
       return true;
    }

    public function updateSortieState(Sortie $entity): bool{

        return true;
    }

    public function findSortiesbyFilter(mixed $data, $userID)
    {
        $query = $this->createQueryBuilder('s')
            ->join('s.site', 'site')
            ->join('s.etat', 'e')
            ->join('s.participants', 'p');
        //TODO: ajouter le filtre sur l'archivage et l'ordre desc

            //filtre pour le site organisateur
            if(!empty($data['site'])){
                $query->andWhere('site.id = :site')
                    ->setParameter('site', $data['site']);
            }

//            //filtre pour la recherche par nom
//            if(!empty($data['search'])){
//                $query->andWhere('s.name LIKE :search')
//                    ->setParameter('search', '%'.$data['search'].'%');
//            }
//
//            //filtre pour la recherche par date de début
//            if(!empty($data['start_date']) && empty($data['end_date'])){
//                $format_start_date = date_format($data['start_date'], 'Y-m-d H:i:s');
//                $query->andWhere('s.dateTimeStart >= :start_date')
//                    ->setParameter('start_date', $format_start_date);
//            }
//
//            //filtre pour la recherche par date de fin
//            if(!empty($data['end_date']) && empty($data['start_date'])){
//                $format_end_date = date_format($data['end_date'], 'Y-m-d H:i:s');
//                $query->andWhere('s.dateTimeStart <= :end_date')
//                    ->setParameter('end_date', $format_end_date);
//            }
//
//            //filtre pour la recherche entre 2 dates
//            if(!empty($data['start_date']) && !empty($data['end_date'])){
//                $format_start_date = date_format($data['start_date'], 'Y-m-d H:i:s');
//                $format_end_date = date_format($data['end_date'], 'Y-m-d H:i:s');
//                $query->andWhere('s.dateTimeStart BETWEEN :start_date AND :end_date')
//                    ->setParameter('start_date', $format_start_date)
//                    ->setParameter('end_date', $format_end_date);
//            }
//
//            //filtre pour savoir si on est organisateur
//            if(!empty($data['organisateur']) ){
//                $query->andWhere('s.organisateur = :organisateur')
//                    ->setParameter('organisateur', $userID);
//            }
//
//            //filtre pour savoir les sorties ou on est inscrit
//            if(!empty($data['inscrit']) && empty($data['non_inscrit'])){
//                $query->andWhere('p.id IN (:inscrit)')
//                    ->setParameter('inscrit', $userID);
//            }
//
//            //filtre pour savoir les sorties ou on ne l'est pas inscrit
//            if(!empty($data['non_inscrit']) && empty($data['inscrit'])){
//                $query->andWhere('p.id NOT IN (:non_inscrit)')
//                    ->setParameter('non_inscrit', $userID);
//            }
//
//            //filtre pour savoir si la sortie est passée
//            if(!empty($data['state']) ){
//                $query->andWhere('s.etat = :state')
//                    ->setParameter('state', $data['Activité passée']);
//            }

            //retourner le résultat
            return $query->getQuery()->getResult();
    }


    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
