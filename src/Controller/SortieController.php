<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Entity\Lieu;
use App\Form\NewLieuType;
use App\Form\NewSortieType;
use App\Form\VilleType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        $userCourant = $this->getUser() == null ? -1 : $this->getUser()->getId();
        $rechercheForm = $this->createFormBuilder(array())
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
            ->add('nomContient', TextType::class, [
                'required' => false
            ])
            ->add('dateDebut', TextType::class, [
                'required' => false
            ])
            ->add('dateFin', TextType::class, [
                'required' => false
            ])
            ->add('organisateur', CheckboxType::class, [
                'required' => false
            ])
            ->add('inscrit', CheckboxType::class, [
                'required' => false
            ])
            ->add('pasInscrit', CheckboxType::class, [
                'required' => false
            ])
            ->add('passees', CheckboxType::class, [
                'required' => false
            ])
            ->add('idUser', HiddenType::class, [
                'attr' => ['value' => $userCourant]
            ])
            ->getForm();
        $rechercheForm->handleRequest($request);

        if ($rechercheForm->isSubmitted() && $rechercheForm->isValid()) {
            $sorties = $repoSorties->rechercherSorties($rechercheForm->getData());
        } else {
            // On gère le cas du filtre non renseigné,
            $sorties = $repoSorties->rechercherSorties(array());
        }

        $sortiesUtilisateur = $repoSorties->listeSortieUtilisateur($userCourant);
        $nbInscritsParSortie = [];
        $listeSites = $repoSites->findAll();
        foreach ($sorties as $sortie) {
            $nbInscritsParSortie[$sortie->getId()] = $repoSorties->nbInscriptions($sortie);
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
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     */
    public function nouvelleSortie(Request $request, EntityManagerInterface $entityManager, EtatRepository $repoEtats)
    {
        $sortie = new Sortie();
        $lieu = new Lieu();
        $ville = new Ville();

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

        if ($newVilleForm->isSubmitted() && $newVilleForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
        }

        if ($newLieuForm->isSubmitted() && $newLieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
        }

        if ($newSortieForm->isSubmitted() && $newSortieForm->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setOrganisateur($organisateur);

            if ($newSortieForm->get('publier')->isClicked()) {
                $etat = $repoEtats->findOneBy(array('libelle' => Etat::OUVERTE));
                $sortie->setEtat($etat);
            } else {
                $etat = $repoEtats->findOneBy(array('libelle' => Etat::CREEE));
                $sortie->setEtat($etat);
            }

            // On gère les dates
            $formatDates = 'd/m/Y H:i';
            $dateSortie = date_create_from_format($formatDates, $newSortieForm->get('dateHeureDebut')->getData());
            $dateLimite = date_create_from_format($formatDates, $newSortieForm->get('dateLimiteInscription')->getData());
            $sortie->setDateHeureDebut($dateSortie);
            $sortie->setDateLimiteInscription($dateLimite);

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash("success", "Votre sortie a bien été créée !");
            return $this->redirectToRoute("sorties");
        }
        return $this->render('sortie/nouvelleSortie.html.twig', [
            'newSortieForm' => $newSortieForm->createView(),
            'newLieuForm' => $newLieuForm->createView(),
            'newVilleForm' => $newVilleForm->createView(),
            'villes' => $villes,
            'lieux' => $lieux
        ]);
    }

    /**
     * @Route("/inscription-sortie/{id}", name="inscriptionSortie")
     */
    public function inscriptionSortie(Request $request, EntityManagerInterface $entityManager, SortieRepository $repoSorties, $id)
    {
        $sortieCourante = $repoSorties->find($id);

        $maintenant = new \DateTime();
        if($sortieCourante->getListeInscrit()->count() < $sortieCourante->getNbInscriptionsMax() && $maintenant<$sortieCourante->getDateLimiteInscription()) {
            $sortieCourante->addEstInscrit($this->getUser());
            $entityManager->persist($sortieCourante);
            $entityManager->flush();
        }
        else{
            if($sortieCourante->getListeInscrit()->count() == $sortieCourante->getNbInscriptionsMax()) {
                $this->addFlash('error', "Nombre maximum d'inscrits atteint");
            }
            if($maintenant>=$sortieCourante->getDateLimiteInscription()) {
                $this->addFlash('error', "Date limite d'inscription dépassée");
            }
        }

        return $this->redirectToRoute("sorties");
    }

    /**
     * @Route("/publier-sortie/{id}", name="publierSortie")
     */
    public function publierSortie(Request $request, EntityManagerInterface $entityManager, SortieRepository $repoSorties, EtatRepository $repoEtats, $id)
    {

        $sortieCourante = $repoSorties->find($id);

        $sortieCourante->setEtat($repoEtats->findOneBy(array('libelle' => Etat::OUVERTE)));
        $entityManager->persist($sortieCourante);
        $entityManager->flush();

        return $this->redirectToRoute("sorties");
    }

    /**
     * @Route("/desister-sortie/{id}", name="desistementSortie")
     */
    public function desistementSortie(Request $request, EntityManagerInterface $entityManager, SortieRepository $repoSorties, $id)
    {
        $sortieCourante = $repoSorties->find($id);

        $sortieCourante->removeEstInscrit($this->getUser());

        $entityManager->persist($sortieCourante);
        $entityManager->flush();

        return $this->redirectToRoute("sorties");
    }

    /**
     * @Route("/annuler-sortie/{id}", name="annulerSortie")
     */
    public function annulerSortie(Request $request, EntityManagerInterface $entityManager, SortieRepository $repoSorties, EtatRepository $repoEtats, $id)
    {

        $sortieCourante = $repoSorties->find($id);
        $formAnnulation = $this->createFormBuilder()->add('commentaireAnnulation', TextareaType::class, [
            'attr' => []
        ])->getForm();

        $formAnnulation->handleRequest($request);

        if ($formAnnulation->isSubmitted() && $formAnnulation->isValid()) {
            $sortieCourante->setEtat($repoEtats->findOneBy(array('libelle' => Etat::ANNULEE)));
            $sortieCourante->setInfosSortie($formAnnulation->getData()['commentaireAnnulation']);
            $entityManager->persist($sortieCourante);
            $entityManager->flush();
            return $this->redirectToRoute("sorties");
        }

        return $this->render('sortie/modaleAnnulation.html.twig', [
            'formAnnulation' => $formAnnulation->createView(),
            'sortie' => $sortieCourante,
        ]);

    }

    /**
     * @Route("/details-sortie/{id}", name="detailsSortie")
     */
    public function detailSortie(Request $request, SortieRepository $repoSorties, $id)
    {
        $sortieCourante = $repoSorties->find($id);

        return $this->render('sortie/DetailsSortie.html.twig',
            ['sortie' => $sortieCourante,
                'repo' => $repoSorties,
            ]);
    }
}