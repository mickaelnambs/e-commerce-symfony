<?php

namespace App\Controller\Back;

use App\Constant\MessageConstant;
use App\Entity\User;
use App\Form\EditUserType;
use App\Constant\PageConstant;
use App\Form\RegistrationType;
use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminAccountController.
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class AdminAccountController extends BaseController
{
    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var UserPasswordEncoderInterface */
    private UserPasswordEncoder $passwordEncoder;

    /**
     * AdminAccountController constructor.
     *
     * @param EntityManagerInterface        $entityManager
     * @param UserPasswordEncoderInterface  $passwordEncoder
     * @param UserRepository                $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        parent::__construct($entityManager);
        $this->userRepository   = $userRepository;
        $this->passwordEncoder  = $passwordEncoder;
    }

    /**
     * Pagination and retrieve all users accounts.
     * 
     * @Route("/admin/accounts/{page<\d+>?1}", name="admin_account_index", methods={"POST","GET"})
     *
     * @param int $page
     * @param PaginationService $pagination
     * 
     * @return Response
     */
    public function index($page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(User::class)
            ->setLimit(PageConstant::DEFAULT_NUMBER_PER_PAGE)
            ->setPage($page);

        return $this->render('admin/account/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Login.
     * 
     * @Route("/admin/login", name="admin_account_login", methods={"POST","GET"})
     *
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('admin/account/login.html.twig', [
            'username' => $authenticationUtils->getLastUsername(),
            'hasError' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * Permet d'ajouter un nouvel utilisateur.
     * 
     * @Route("/admin/register", name="admin_account_register", methods={"POST","GET"})
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

            if ($this->save($user)) {
                $this->addFlash(
                    MessageConstant::SUCCESS_TYPE,
                    "Votre compte a bien été créé ! Vous pouvez maintenant vous connecter !"
                );
                return $this->redirectToRoute('account_login');
            }
            $this->addFlash(
                MessageConstant::ERROR_TYPE,
                "Il y a un probleme pendant l'inscription ! Veuiller reessayer !"
            );
            return $this->redirectToRoute('admin_account_register');
        }
        return $this->render('admin/account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Logout.
     * 
     * @Route("/admin/logout", name="admin_account_logout")
     *
     * @return void
     */
    public function logout()
    {
        // empty.
    }

    /**
     * Edit user account.
     * 
     * @Route("/admin/{id}/edit", name="admin_account_edit", methods={"POST","GET"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param Request $request
     * 
     * @return Response
     */
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsModified(true);
            if ($this->save($user)) {
                $this->addFlash(
                    MessageConstant::SUCCESS_TYPE,
                    "Le compte de {$user->getFirstName()} a bien été modifié !"
                );
                return $this->redirectToRoute('admin_account_index');
            }
            $this->addFlash(
                MessageConstant::ERROR_TYPE,
                "Il y a un probleme pendant la modification de votre compte ! Veuillez reessayer !"
            );
            return $this->redirectToRoute('admin_account_edit', ['id' => $user->getId()]);
        }
        return $this->render('admin/account/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Remove user.
     * 
     * @Route("/admin/accounts/{id}/delete", name="admin_account_delete")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * 
     * @return Response
     */
    public function delete(User $user): Response
    {
        if ($this->remove($user)) {
            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "L'utilisateur {$user->getFirstName()} a bien été supprimé !"
            );
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }
        return $this->redirectToRoute('admin_account_index');
    }
}
