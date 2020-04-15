<?php

namespace App\Repository;

use App\Entity\Sortie;
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

    public function listeSortieParSite($site)
    {
        $maintenant = time();
        // test
        if($site == null or $site=="tous_les_sites"){
            return $this->createQueryBuilder('s')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->orderBy('s.dateHeureDebut', 'DESC')
                ->setParameters(array('maintenant'=>$maintenant))
                ->getQuery()
                ->getResult();
        }
        else{
            return $this->createQueryBuilder('s')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->andWhere('s.organisateur.site = :site')
                ->orderBy('s.dateHeureDebut', 'DESC')
                ->setParameters(array('site' => $site, 'maintenant'=>$maintenant))
                ->getQuery()
                ->getResult();
        }
    }

}
