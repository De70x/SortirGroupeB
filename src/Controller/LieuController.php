<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\NewLieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/nouveau-lieu", name="nouveauLieu")
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
