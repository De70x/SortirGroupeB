<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties/list/{site}", name="sorties")
     */
    public function listeSorties(SortieRepository $repoSorties , $site=-1)
    {
        $sorties = $repoSorties->listeSortieParSite($site);
        $sortiesUtilisateur = $repoSorties->listeSortieUtilisateur($this->getUser()->getId());

        dump($sortiesUtilisateur);
        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'site' => $site,
            'sortiesUtilisateur' => $sortiesUtilisateur,
            'controller_name' => 'SortieController',
        ]);
    }
}
