<?php

namespace App\Repository;

use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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

    public function listeSortieParSite($idSite)
    {
        $maintenant = new DateTime();
        // test
        if($idSite == null or $idSite==-1){
            return $this->createQueryBuilder('s')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->orderBy('s.dateHeureDebut', 'DESC')
                ->setParameters(array('maintenant'=>$maintenant))
                ->getQuery()
                ->getResult();
        }
        else{
            return $this->createQueryBuilder('s')
                ->leftJoin('s.organisateur', 'org')
                ->leftJoin('org.site', 'siteOrg')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->andWhere('siteOrg.id = :idSite')
                ->orderBy('s.dateHeureDebut', 'DESC')
                ->setParameters(array('idSite' => $idSite, 'maintenant'=>$maintenant))
                ->getQuery()
                ->getResult();
        }
    }

    public function listeSortieUtilisateur($user)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.estInscrit', 'inscriptions')
            ->andWhere('inscriptions.id = :user')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setParameters(array('user' => $user))
            ->getQuery()
            ->getResult();
    }
}
