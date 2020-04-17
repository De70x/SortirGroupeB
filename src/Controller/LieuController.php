<?php

namespace App\Controller;

use App\Entity\Lieu;
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
        $newLieuForm = $this->createForm(NewLieuType::class);


        if ($newLieuForm->isSubmitted() && $newLieuForm->isValid()){
            $entityManager->persist($lieu);
            $entityManager->flush();
        }

        return $this->render('lieu/nouveauLieu.html.twig', [
            'controller_name' => 'LieuController',
            'newLieuForm'=>$newLieuForm->createView()
        ]);
    }
}