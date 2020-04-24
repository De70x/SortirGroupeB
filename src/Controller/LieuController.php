<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\NewLieuType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/nouveau-lieu", name="nouveauLieu")
     * @throws \JsonException
     */
    public function nouveauLieu(Request $request, EntityManagerInterface $entityManager)
    {
        $lieu = new Lieu();
        $newLieuForm = $this->createForm(NewLieuType::class, $lieu);

        if($request->isXmlHttpRequest()){
            $nom = $request->get('nom');
            $rue = $request->get('rue');
            $villeId = $request->get('ville');

            $villeRepo = $entityManager->getRepository(Ville::class);
            $ville = $villeRepo->findOneBy(array(
                'id'=>$villeId
            ));
            $lieu->setNom($nom);
            $lieu->setRue($rue);
            $lieu->setVille($ville);
            $entityManager->persist($lieu);
            $entityManager->flush();
            unset($lieu);
            unset($newLieuForm);
            $lieu = new Lieu();

            $lieuRepo = $entityManager->getRepository(Lieu::class);
            $lieuContent = $lieuRepo->findAll();
            $contentArray = [];

            foreach ($lieuContent as $lieu){
                $id = $lieu->getId();
                $nom = $lieu->getNom();
                array_push($contentArray,['id'=>[$id],'nom'=>[$nom]]);
            }

            $lieux = json_encode($contentArray,JSON_THROW_ON_ERROR, 3);
            dump($lieux);
            return new JsonResponse($lieux);
        }
        $newLieuForm->handleRequest($request);

        if ($newLieuForm->isSubmitted() && $newLieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash("success", "le lieu a bien été créé !");
            return $this->redirectToRoute("listeLieux");
        }
        return $this->render('lieu/nouveauLieu.html.twig', [
            'controller_name' => 'LieuController',
            'newLieuForm'=>$newLieuForm->createView()
        ]);
    }

    /**
     * @Route("admin/lieux/liste", name="listeLieux")
     * @param LieuRepository $lieuRepo
     * @return Response
     */
    public function listeLieu(LieuRepository $lieuRepo)
    {
        $lieux = $lieuRepo->findAll();
        return $this->render('lieu/liste.html.twig', [
            'Lieux' => $lieux,
        ]);
    }

    /**
     * @Route("admin/lieux/supprimer/{id}", name="supprimerLieu")
     * @param $id
     * @param LieuRepository $lieuRepo
     * @param SortieRepository $sortieRepo
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function supprimerLieu($id, LieuRepository $lieuRepo, SortieRepository $sortieRepo, EntityManagerInterface $em)
    {

        $lieu = $lieuRepo->find($id);
        $sorties = $sortieRepo->findByLieu($lieu);

        if ($sorties != null) {
            foreach ($sorties as $sortie) {
                $em->remove($sortie);
            }
        }

        $em->remove($lieu);
        $em->flush();


        return $this->redirectToRoute('listeLieux');

    }




}
