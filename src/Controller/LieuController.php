<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\NewLieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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


        $newLieuForm = $this->createForm(NewLieuType::class, $lieu);
        $newLieuForm->handleRequest($request);

        if ($newLieuForm->isSubmitted() && $newLieuForm->isValid()){
           dump($newLieuForm->getData());
            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash("success", "Votre lieu à bien été créé !");
            return $this->redirectToRoute("nouveauLieu");
        }

        return $this->render('lieu/nouveauLieu.html.twig', [
            'controller_name' => 'LieuController',
            'newLieuForm'=>$newLieuForm->createView()
        ]);
    }
}
