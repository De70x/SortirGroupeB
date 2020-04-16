<?php

namespace App\Repository;

use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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

    /**
     * @return Sortie[]
     * @throws Exception
     */
    public function listeSortieParSite($idSite)
    {
        $maintenant = new DateTime();
        // test
        if($idSite == null or $idSite==-1){
            return $this->createQueryBuilder('s')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->orderBy('s.dateHeureDebut', 'ASC')
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
                ->orderBy('s.dateHeureDebut', 'ASC')
                ->setParameters(array('idSite' => $idSite, 'maintenant'=>$maintenant))
                ->getQuery()
                ->getResult();
        }
    }

    public function listeSortieUtilisateur($userId)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.inscriptions', 'i')
            ->andWhere('i.id = :userId')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setParameters(array('userId' => $userId))
            ->getQuery()
            ->getResult();
    }

    public function nbInscriptions(Sortie $sortie)
    {
        $nombreInscrits = 0;
        try {
            $nombreInscrits =  $this->createQueryBuilder('s')
                ->leftJoin('s.inscriptions', 'i')
                ->select('COUNT(i.id)')
                ->andWhere('s.id = :sortieId')
                ->setParameters(array('sortieId' => $sortie->getId()))
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        } finally {
            return $nombreInscrits;
        }
    }
}
