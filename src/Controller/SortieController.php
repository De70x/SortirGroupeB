<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\NewSortieType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sorties/list", name="sorties")
     * @param SortieRepository $repoSorties
     * @param SiteRepository $repoSites
     * @return Response
     * @throws Exception
     */
    public function listeSorties(SortieRepository $repoSorties, SiteRepository $repoSites, Request $request)
    {
        // On crée le formulaire de recherche
        $filtres = array();
        $rechercheForm = $this->createFormBuilder($filtres)
            ->add('site', EntityType::class, [
                'class' => 'App\Entity\Site',
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('site')->orderBy('site.nom', 'ASC');
                },
                'choice_label' => function (Site $site) {
                    return $site->getNom();
                },
                'placeholder' => 'Choose an option',
            ])
            ->add('nomContient')
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                // on retire le picker par defaut de html5
                'html5' => false,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('organisateur', CheckboxType::class)
            ->add('inscrit', CheckboxType::class)
            ->add('pasInscrit', CheckboxType::class)
            ->add('passees', CheckboxType::class)
            ->getForm();
        $rechercheForm->handleRequest($request);
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            dump($rechercheForm->getData());
            $sorties = $repoSorties->findAll();
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
        }
        else{
            // On gère le cas du filtre non renseigné
            $sorties = $repoSorties->listeSortieParSite($this->getUser()->getSite()->getId());
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
        }


        return $this->render('sortie/liste.html.twig', [
            'rechercheForm' => $rechercheForm->createView(),
            'sorties' => $sorties,
            'listeSites' => $listeSites,
            'sortiesUtilisateur' => $sortiesUtilisateur,
            'nbInscritsParSortie' => $nbInscritsParSortie,
        ]);
    }

    /**
     * @Route("/nouvelle-sortie", name="nouvelleSortie")
     *
     */
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager){
        $sortie = new Sortie();
        $newSortieForm = $this->createForm(NewSortieType::class, $sortie);


        if ($newSortieForm->isSubmitted() && $newSortieForm->isValid()){
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->render('sortie/nouvelleSortie.html.twig',[
            'newSortieForm'=>$newSortieForm->createView()
        ]);
    }
}
