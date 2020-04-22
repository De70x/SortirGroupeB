<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Form\ProfileFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;


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
     * @Route("/admin/register", name="register")
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
     * @Route("/change_profile", name="change_profile")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function profile(Request $request, EntityManagerInterface $em, UserRepository $userRepo, SluggerInterface $slugger, UserPasswordEncoderInterface $encoder){

        //$user = $userRepo->find($id);
        $user = $this->getUser();
        $profileForm = $this->createForm(ProfileFormType::class, $user);
        $user->setAdministrateur(false);

        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $userPhoto = $profileForm->get('photoFile')->getData();
            if ($userPhoto) {
                $photoFileName = pathinfo($userPhoto->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($photoFileName);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$userPhoto->guessExtension();

                try {
                    $userPhoto->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }
                $user->setPhoto($newFilename);
            }
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "Votre compte à bien été modifié !");
            return $this->redirectToRoute("profile", [
                'id' => $user->getId(),
            ]);
        }


        return $this->render("user/changeProfile.html.twig", [
            'profileForm' => $profileForm->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}", name="profile")
     */
    public function userProfile($id, UserRepository $userRepo) {

        $user = $userRepo->find($id);
        return $this->render("user/profile.html.twig", ['utilisateur'=>$user]);
    }

    /**
     * @Route("/reset_password/{token}", name="reset_password")
     */
    public function forgotPassword(Request $request, UserPasswordEncoderInterface $encoder, string $token = "0", EntityManagerInterface $em) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneByResetToken($token);
        if ($request->isMethod('POST')) {

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('login');
            }

            $user->setResetToken(null);
            $user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
            $em->flush();

            $this->addFlash('notice', 'Mot de passe mis à jour');

            return $this->redirectToRoute('home');
        }else {
            return $this->render('user/resetPassword.html.twig', ['token' => $token]);
        }

    }



    /**
     * @Route("/forgot_password", name="forgot_password")
     */
    public function forgottenPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $token = $tokenGenerator->generateToken();
        $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByMail($email);

            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->render('base.html.twig');
            }


            try{
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->render('base.html.twig');
            }



            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('test@michel.michel.com')
                ->setTo($user->getMail())
                ->setBody(
                    $this->renderView(
                        'user/resetPassword2.html.twig',
                        [
                            'token'=>$token,
                            'url'=>$url
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('notice', 'Mail envoyé');

            return $this->render('base.html.twig');
        }

        return $this->render('base.html.twig');
    }
    /**
     * @Route("/reset", name="reset")
     */
    public function reset() {
        return $this->render('user/resetPassword2.html.twig', ['token'=>'unechaine', 'url'=>'unechaine2']);
    }
}
