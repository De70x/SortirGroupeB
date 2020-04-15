<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\User;
use App\Form\RegisterType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authUtils) {

        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('user/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder) {

        $user = new User();
        $registerForm = $this->createForm(RegisterType::class, $user);
        $user->setAdministrateur(false);

        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {

            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "Votre compte à bien été créé !");
            return $this->redirectToRoute("home");

        }
        return $this->render('user/register.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(EntityManagerInterface $em){

        $user = new User();


        return $this->render("user/profile.html.twig");
    }



}
