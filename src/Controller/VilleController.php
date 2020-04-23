<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\NewLieuType;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class VilleController extends AbstractController
{
    /**
     * @Route("/nouvelle-ville", name="nouvelleVille")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse|Response
     * @throws \JsonException
     */
    public function nouvelleVille(Request $request, EntityManagerInterface $entityManager)
    {
        $ville = new Ville();
        $newVilleForm = $this->createForm(VilleType::class, $ville);

        if ($request->isXmlHttpRequest()) {
            $nom = $request->get('nom');
            $codePostal = $request->get('codePostal');

            $ville->setNom($nom);
            $ville->setCodePostal($codePostal);
            $entityManager->persist($ville);
            $entityManager->flush();

            unset($ville);
            unset($newVilleForm);
            $ville = new Ville();

            $villeRepo = $entityManager->getRepository(Ville::class);
            $villeContent = $villeRepo->findAll();
            $contentArray = [];

            foreach ($villeContent as $ville) {
                $id = $ville->getId();
                $nom = $ville->getNom();
                array_push($contentArray, ['id' => [$id], 'nom' => [$nom]]);
            }

            $villes = json_encode($contentArray, JSON_THROW_ON_ERROR, 3);
            dump($villes);
            return new JsonResponse($villes);

        }
        $newVilleForm->handleRequest($request);
        if ($newVilleForm->isSubmitted() && $newVilleForm->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();
            $this->addFlash("success", "la ville a bien été créé !");
            return $this->redirectToRoute("listeUtilisateurs");

        }

        return $this->render('ville/nouvelleVille.html.twig', [
            'controller_name' => 'VilleController',
            'newVilleForm' => $newVilleForm->createView()
        ]);
    }

    /**
     * @Route("admin/villes/liste", name="listeVilles")
     */
    public function listeVille(VilleRepository $villeRepo)
    {
        $villes = $villeRepo->findAll();
        return $this->render('ville/liste.html.twig', [
            'villes' => $villes,
        ]);
    }

    /**
     * @Route("admin/villes/supprimer/{id}", name="supprimerVille")
     * @param $id
     * @param VilleRepository $villeRepo
     * @param LieuRepository $lieuRepo
     * @param SortieRepository $sortieRepo
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function supprimerVille($id, VilleRepository $villeRepo, LieuRepository $lieuRepo, SortieRepository $sortieRepo, EntityManagerInterface $em)
    {
        $ville = $villeRepo->find($id);
        $lieux = $lieuRepo->findByVille($ville);
        $sorties = [];
        foreach ($lieux as $lieu){
            array_merge($sorties, $sortieRepo->findByLieu($lieu));
        }

        if ($sorties != null) {
            foreach ($sorties as $sortie) {
                $em->remove($sortie);
            }
        }
        if ($lieux != null) {
            foreach ($lieux as $lieu) {
                $em->remove($lieu);
            }
        }

        $em->remove($ville);
        $em->flush();

        $villes = $villeRepo->findAll();

        return $this->render('ville/liste.html.twig', [
            'villes' => $villes,
        ]);

    }
}