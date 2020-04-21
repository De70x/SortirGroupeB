<?php

namespace App\Repository;

use App\Entity\Etat;
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
        if ($idSite == null or $idSite == -1) {
            return $this->createQueryBuilder('s')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->orderBy('s.dateHeureDebut', 'ASC')
                ->setParameters(array('maintenant' => $maintenant))
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('s')
                ->leftJoin('s.organisateur', 'org')
                ->leftJoin('org.site', 'siteOrg')
                ->andWhere('s.dateLimiteInscription > :maintenant')
                ->andWhere('siteOrg.id = :idSite')
                ->orderBy('s.dateHeureDebut', 'ASC')
                ->setParameters(array('idSite' => $idSite, 'maintenant' => $maintenant))
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
            $nombreInscrits = $this->createQueryBuilder('s')
                ->leftJoin('s.inscriptions', 'i')
                ->select('COUNT(i.id)')
                ->andWhere('s.id = :sortieId')
                ->setParameter('sortieId', $sortie->getId())
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        } finally {
            return $nombreInscrits;
        }
    }

    /**
     * @param $filtres : un tableau contenant tous les filtres. Les champs présents dans ce filtre sont :
     * $filtres['site'] : le site sélectionné dans la combobox
     * $filtres['nomContient'] : La chaine de caractère tapée dans le champ de recherche par nom
     * $filtres['dateDebut'] : La date à partir de laquelle on recherche
     * $filtres['dateFin'] : La date jusqu'à laquelle on recherche
     * $filtres['organisateur'] : la coche sorties que j'organise
     * $filtres['inscrit'] : la coche sorties où je suis inscrit
     * $filtres['pasInscrit'] : la coche sorties où je suis inscrit
     * $filtres['passees'] : la coche sorties passées
     * $filtres['idUser'] : identifiant de l'utilisateur connecté
     * @return Sortie[]
     * @throws Exception
     */
    public function rechercherSorties($filtres)
    {
        dump($filtres);
        // filtre par défaut
        if (empty($filtres)) {
            $rechercheAvancee = $this->createQueryBuilder('s')
                ->andWhere('s.etat != :cree')
                ->setParameter('cree', Etat::CREEE);
        } else {

            $formatDates = 'd/m/Y H:i';
            $rechercheAvancee = $this->createQueryBuilder('s')
                ->leftJoin('s.inscriptions', 'i');

            if ($filtres['site'] != null) {
                $rechercheAvancee->andWhere('s.id = :idSite')
                    ->setParameter('idSite', $filtres['site']->getId());
            }

            if ($filtres['nomContient'] != null) {
                $rechercheAvancee->andWhere('s.nom LIKE :nomContient')
                    ->setParameter('nomContient', '%' . $filtres['nomContient'] . '%');
            }

            if ($filtres['dateDebut'] != null) {
                // On formate la date car c'est une chaine de caractère qu'on récupère du formulaire
                $dateDebut = date_create_from_format($formatDates, $filtres['dateDebut']);
                $rechercheAvancee->andWhere('s.dateHeureDebut >= :dateDebut')
                    ->setParameter('dateDebut', $dateDebut);
            }

            if ($filtres['dateFin'] != null) {
                // On formate la date car c'est une chaine de caractère qu'on récupère du formulaire
                $dateFin = date_create_from_format($formatDates, $filtres['dateFin']);
                $rechercheAvancee->andWhere('s.dateHeureDebut <= :dateFin')
                    ->setParameter('dateFin', $dateFin);
            }

            $queryCoches = $this->createQueryBuilder('sc')
                ->leftJoin('sc.inscriptions', 'sci');

            // ici on va gérer les coches
            if ($filtres['idUser'] != null) {
                if ($filtres['organisateur']) {
                    $queryCoches->orWhere('sc.organisateur = :idOrg');
                    $rechercheAvancee->setParameter('idOrg', $filtres['idUser']);
                }
                if ($filtres['inscrit']) {
                    $queryCoches->orWhere('sci.id = :idUser');
                    $rechercheAvancee->setParameter('idUser', $filtres['idUser']);
                }
                if ($filtres['pasInscrit']) {
                    // On passe par une requête intermédiare pour faire le not in
                    $idMesSorties = $this->createQueryBuilder('ms')
                        ->leftJoin('ms.inscriptions', 'mi')
                        ->where($queryCoches->expr()->eq('mi.id', $filtres['idUser']));

                    $queryCoches->orWhere($queryCoches->expr()->notIn('sc.id', $idMesSorties->getDQL()));
                }
                if ($filtres['passees']) {
                    $maintenant = new DateTime();
                    $queryCoches->orWhere('sc.dateHeureDebut <= :maintenant');
                    $rechercheAvancee->setParameter('maintenant', $maintenant);
                }
            }
            $rechercheAvancee->andWhere($rechercheAvancee->expr()->in('s.id', $queryCoches->getDQL()));
        }
        return $rechercheAvancee->orderBy('s.dateHeureDebut', 'ASC')->getQuery()->getResult();
    }
}
