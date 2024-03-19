<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
       if($entity->getMaxInscriptionNb() > count($entity->getParticipants()) && $entity->getEtat()->getName()==='Ouverte'){
        $entity->addParticipant($participant);
       $entityManager->persist($entity);
       $entityManager->flush();
       return true;
       }else{
           return false;
       }
    }

    public function unSubscribe(Sortie $entity,EntityManagerInterface $entityManager, Participant $participant): bool{
        $etat = $entity->getEtat()->getName();
        if($etat === 'Ouverte'|| $etat === 'Clôturée'){
            $entity->removeParticipant($participant);
            $entityManager->persist($entity);
            $entityManager->flush();
            return true;
        }else{
            return false;
        }
    }

    public function updateSortieState($tabSortie,EntityManagerInterface $entityManager,EtatRepository $etatRepository): bool
    {
        foreach ($tabSortie as $entity) {
            $now = new \DateTime('now');
            $now->modify('+1 hour');
            if ($entity->getEtat()->getName() === 'Ouverte') {
                if ($entity->getDateInscriptionLimit() < $now) {
                    $entity->setEtat($etatRepository->findOneBy(['name' => 'Clôturée']));
                }
            }
            $dateFinAc = clone $entity->getDateTimeStart();
            $dateFinAc->modify('+'.$entity->getDuration().' minutes');
            if ($entity->getEtat()->getName() === 'Clôturée') {
                if ($entity->getDateTimeStart()<=$now && $dateFinAc >= $now) {
                    $entity->setEtat($etatRepository->findOneBy(['name' => 'Activité en cours']));
                }else if($dateFinAc<=$now){
                    $entity->setEtat($etatRepository->findOneBy(['name' => 'Passée']));
                }
            }
            if($entity->getEtat()->getName()==='Activité en cours'){
                if ($dateFinAc<=$now) {
                    $entity->setEtat($etatRepository->findOneBy(['name' => 'Passée']));
                }
            }

            if ($entity->getEtat()->getName() === 'Passée' || $entity->getEtat()->getName() === 'Annulée') {
                if ($dateFinAc->modify('+1 month') < $now) {
                    $entity->setEtat($etatRepository->findOneBy(['name' => 'Archivée']));
                }
            }
            $entityManager->persist($entity);
            $entityManager->flush();
        }
        return true;
    }

//    public function findByStates(Participant $participant){
//        return $this->createQueryBuilder('s')
//            ->orWhere("s.etat = 2")
//            ->orWhere("s.etat = 3")
//            ->orWhere("s.etat = 4")
//            ->orWhere("s.etat = 5")
//            ->orWhere("s.etat = 6")
//            ->orWhere("s.etat = 1 and s.organisateur = :id")
//            ->setParameter('id', $participant->getId())
//            ->getQuery()
//        ->getResult();
//    }

    public function publish(Sortie $entity,EntityManagerInterface $entityManager): bool{
        $entity->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['name' => 'Ouverte']));
        $entityManager->persist($entity);
        $entityManager->flush();
        return true;
    }

    public function findSortiesbyFilter(mixed $data, $userID,$page)
    {
        $maxResults=6;
        $query = $this->createQueryBuilder('s')
            ->join('s.site', 'site')
            ->join('s.etat', 'e')
            ->leftJoin('s.participants', 'p');

            //filtre pour le site organisateur
            if(!empty($data['site'])){
                $query->andWhere('site.id = :site')
                    ->setParameter('site', $data['site']);
            }

            //filtre pour la recherche par nom
            if(!empty($data['search'])){
                $query->andWhere('s.name LIKE :search')
                    ->setParameter('search', '%'.$data['search'].'%');
            }

            //filtre pour la recherche par date de début
            if(!empty($data['start_date']) && empty($data['end_date'])){
                $format_start_date = date_format($data['start_date'], 'Y-m-d H:i:s');
                $query->andWhere('s.dateTimeStart >= :start_date')
                    ->setParameter('start_date', $format_start_date);
            }

            //filtre pour la recherche par date de fin
            if(!empty($data['end_date']) && empty($data['start_date'])){
                $format_end_date = date_format($data['end_date'], 'Y-m-d H:i:s');
                $query->andWhere('s.dateTimeStart <= :end_date')
                    ->setParameter('end_date', $format_end_date);
            }

            //filtre pour la recherche entre 2 dates
            if(!empty($data['start_date']) && !empty($data['end_date'])){
                $format_start_date = date_format($data['start_date'], 'Y-m-d H:i:s');
                $format_end_date = date_format($data['end_date'], 'Y-m-d H:i:s');
                $query->andWhere('s.dateTimeStart BETWEEN :start_date AND :end_date')
                    ->setParameter('start_date', $format_start_date)
                    ->setParameter('end_date', $format_end_date);
            }

            //filtre pour savoir si on est organisateur
            if(!empty($data['organisateur']) ){
                $query->andWhere('s.organisateur = :organisateur')
                    ->setParameter('organisateur', $userID);
            }

            //filtre pour savoir les sorties ou on est inscrit
            if(!empty($data['inscrit']) && empty($data['non_inscrit'])){
                $query->andWhere('p.id IN (:inscrit)')
                    ->setParameter('inscrit', $userID);
            }

            //filtre pour savoir les sorties ou on ne l'est pas inscrit
            if(!empty($data['non_inscrit']) && empty($data['inscrit'])){
                $query->andWhere('p.id NOT IN (:non_inscrit) OR p.id IS NULL')

                    ->setParameter('non_inscrit', $userID);
            }

            //filtre pour savoir si la sortie est passée
            if(!empty($data['state']) ){
                $query->andWhere("s.etat = 5");

            };
        $query->andWhere("s.etat = 1 and s.organisateur = :id or s.etat between 2 and 6")
            ->setParameter('id', $userID)
            ->orderBy('s.etat', 'ASC')
            ->setFirstResult($maxResults*($page-1))
            ->setMaxResults($maxResults);
            //retourner le résultat

             return [$query->getQuery()->getResult(),count(new Paginator($query,true))];
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
