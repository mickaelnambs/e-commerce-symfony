<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\EditUserType;
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
     * @Route("/admin/accounts/{page<\d+>?1}", name="admin_account_index")
     *
     * @param int $page
     * @param PaginationService $pagination
     * 
     * @return Response
     */
    public function index($page, PaginationService $pagination)
    {
        $pagination->setEntityClass(User::class)
            ->setLimit(5)
            ->setPage($page);

        return $this->render('admin/account/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Login.
     * 
     * @Route("/admin/login", name="admin_account_login")
     *
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        return $this->render('admin/account/login.html.twig', [
            'username' => $authenticationUtils->getLastUsername(),
            'hasError' => $authenticationUtils->getLastAuthenticationError()
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
     * @Route("/admin/{id}/edit", name="admin_account_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     *
     * @param User $user
     * @param Request $request
     * 
     * @return Response
     */
    public function edit(User $user, Request $request)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setIsModified(true);
            $this->save($user);

            $this->addFlash(
                'success',
                "L'utilisateur {$user->getFirstName()} a bien été modifié !"
            );
            return $this->redirectToRoute('admin_account_index');
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
    public function delete(User $user)
    {
        if ($user) {
            $this->remove($user);
            $this->addFlash(
                'success',
                "L'utilisateur {$user->getFirstName()} a bien été supprimé !"
            );
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }
        return $this->redirectToRoute('admin_account_index');
    }
}
