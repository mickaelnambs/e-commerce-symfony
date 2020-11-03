<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use App\Constant\MessageConstant;
use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AccountController.
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class AccountController extends BaseController
{
    /** @var UserPasswordEncoderInterface */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * AccountController constructor.
     *
     * @param EntityManagerInterface        $entityManager
     * @param UserPasswordEncoderInterface  $passwordEncoder
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($entityManager);
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * User inscription.
     * 
     * @Route("/register", name="account_register", methods={"GET|POST"})
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function registration(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            $file = $form->get('image')->getData();
            $this->uploadFile($file, $user);

            $this->save($user);
            
            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Votre compte a bien été créé ! Vous pouvez maintenant vous connecter !"
            );
            return $this->redirectToRoute('account_login');
        }
        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Login.
     * 
     * @Route("/login", name="account_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('account/login.html.twig', [
            'username' => $authenticationUtils->getLastUsername(),
            'hasError' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * Logout.
     * 
     * @Route("/logout", name="account_logout")
     *
     * @return void
     */
    public function logout()
    {
        // Empty.
    }

    /**
     * Modify the user profile.
     * 
     * @Route("/account/profile", name="account_profile", methods={"GET|POST"})
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function profile(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            $user->setIsModified(true);

            $this->uploadFile($file, $user);
            $this->save($user);

            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Les données du profil ont été enregistrée avec succès !"
            );
        }
        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Change password.
     * 
     * @Route("/account/change-password", methods="GET|POST", name="account_password")
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function changePassword(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $form->get('newPassword')->getData()));

            $this->save($user);

            return $this->redirectToRoute('account_logout');
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show profile user connected.
     *
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function myAccount(): Response
    {
        return $this->render('account/index.html.twig', [
            'users' => $this->getUser(),
        ]);
    }
}
