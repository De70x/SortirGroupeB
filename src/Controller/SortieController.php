<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties/list/{site}", name="sorties")
     * @param SortieRepository $repoSorties
     * @param int $site
     * @param SiteRepository $repoSites
     * @return Response
     * @throws Exception
     */
    public function listeSorties(SortieRepository $repoSorties, SiteRepository $repoSites, $site=-1)
    {
        $sorties = $repoSorties->listeSortieParSite($site);
        $user=0;
        if($this->getUser() != null){
            $user = $this->getUser()->getId();
        }
        $sortiesUtilisateur = $repoSorties->listeSortieUtilisateur($user);
        $nbInscritsParSortie = [];
        $listeSites = $repoSites->findAll();

        foreach ($sorties as $sortie){
            $nbInscritsParSortie[$sortie->getId()] = $repoSorties->nbInscriptions($sortie);
        }

        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'site' => $site,
            'listeSites' => $listeSites,
            'sortiesUtilisateur' => $sortiesUtilisateur,
            'nbInscritsParSortie' => $nbInscritsParSortie,
            'controller_name' => 'SortieController',
        ]);
    }
}
