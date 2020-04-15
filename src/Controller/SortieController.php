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
    public function listeSorties(SortieRepository $repoSorties , $site="tous_les_sites")
    {

        $sorties = $repoSorties->listeSortieParSite($site);
        dump($sorties);
        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'site' => $site,
            'controller_name' => 'SortieController',
        ]);
    }
}
