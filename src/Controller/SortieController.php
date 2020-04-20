<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\NewLieuType;
use App\Form\NewSortieType;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use http\Client\Curl\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $userCourant = $this->getUser()->getId() == null ? -1 : $this->getUser()->getId();
        $rechercheForm = $this->createFormBuilder($filtres)
            ->add('site', EntityType::class, [
                'class' => 'App\Entity\Site',
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder('site')->orderBy('site.nom', 'ASC');
                },
                'choice_label' => function (Site $site) {
                    return $site->getNom();
                },
                'placeholder' => 'Tous les sites',
                'required' => false
            ])
            ->add('nomContient', TextType::class,[
                'required' => false
            ])
            ->add('dateDebut', TextType::class,[
                'required' => false
            ])
            ->add('dateFin', TextType::class,[
                'required' => false
            ])
            ->add('organisateur', CheckboxType::class,[
                'required' => false
            ])
            ->add('inscrit', CheckboxType::class,[
                'required' => false
            ])
            ->add('pasInscrit', CheckboxType::class,[
                'required' => false
            ])
            ->add('passees', CheckboxType::class,[
                'required' => false
            ])
            ->add('idUser', HiddenType::class,[
                'attr' => ['value' => $userCourant]
            ])
            ->getForm();
        $rechercheForm->handleRequest($request);
        dump($rechercheForm->getData());
        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()){
            $sorties = $repoSorties->rechercherSorties($rechercheForm->getData());
        }
        else{
            // On gère le cas du filtre non renseigné,
            $sorties = $repoSorties->listeSortieParSite(-1);

        }

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
            'rechercheForm' => $rechercheForm->createView(),
            'sorties' => $sorties,
            'listeSites' => $listeSites,
            'sortiesUtilisateur' => $sortiesUtilisateur,
            'nbInscritsParSortie' => $nbInscritsParSortie,
            'etatSortie'=>$sortie->getEtat()->getLibelle(),
            'sortieOrganisateur'=>$sortie->getOrganisateur()->getNom(),

        ]);
    }

    /**
     * @Route("/nouvelle-sortie", name="nouvelleSortie")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager){

        $sortie = new Sortie();
        $lieu = new Lieu();
        $ville= new Ville();

        $repoVille = $entityManager->getRepository(Ville::class);
        $villes = $repoVille->findAll();
        $repoLieu = $entityManager->getRepository(Lieu::class);
        $lieux = $repoLieu->findAll();

        $newSortieForm = $this->createForm(NewSortieType::class, $sortie);
        $newLieuForm = $this->createForm(NewLieuType::class, $lieu);
        $newVilleForm = $this->createForm(VilleType::class, $ville);


        $newSortieForm->handleRequest($request);
        $newLieuForm->handleRequest($request);
        $newVilleForm->handleRequest($request);


        if ($newVilleForm->isSubmitted() && $newVilleForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
        }

        if($newLieuForm->isSubmitted() && $newLieuForm->isValid()){
            $entityManager->persist($lieu);
            $entityManager->flush();
        }


        if ($newSortieForm->isSubmitted() && $newSortieForm->isValid()){
            $etat = new Etat();
            $sortie->setEtat($etat);
            $organisateur = $this->getUser();
            $sortie->setOrganisateur($organisateur);

            if ($newSortieForm->get('publier')->isClicked()){
                $etat->setLibelle(etat::OUVERTE);
            }else{
                $etat->setLibelle(etat::CREEE);
            }
            $entityManager->persist($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash("success", "Votre compte à bien été créé !");
            return $this->redirectToRoute("nouvelleSortie");
        }
        return $this->render('sortie/nouvelleSortie.html.twig',[
            'newSortieForm'=>$newSortieForm->createView(),
            'newLieuForm'=>$newLieuForm->createView(),
            'newVilleForm'=>$newVilleForm->createView(),
            'villes'=>$villes,
            'lieux'=>$lieux
        ]);
    }

    /**
     * @Route("/inscription-sortie", name="inscriptionSortie")
     */
    public function inscriptionSortie(Request $request, EntityManagerInterface $entityManager){

        return $this->render('sortie/liste.html.twig',[

        ]);
    }
}
