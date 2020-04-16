<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("/nouvelle-ville", name="nouvelleVille")
     */
    public function nouvelleVille(Request $request, EntityManagerInterface $entityManager)
    {
        $ville = new Ville();
        $newVilleForm = $this->createForm(VilleType::class, $ville);

        $newVilleForm->handleRequest($request);
        if ($newVilleForm->isSubmitted() && $newVilleForm->isValid()){
            $entityManager->persist($ville);
            $entityManager->flush();
        }
        return $this->render('ville/nouvelleVille.html.twig', [
            'controller_name' => 'VilleController',
            'newVilleForm'=>$newVilleForm->createView()
        ]);
    }
}
