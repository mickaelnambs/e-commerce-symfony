<?php

namespace App\Controller\Back;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Constant\PageConstant;
use App\Constant\MessageConstant;
use App\Controller\BaseController;
use App\Service\PaginationService;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AdminCategoryController.
 * 
 * @Route("/admin/categories")
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class AdminCategoryController extends BaseController
{
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;

    /**
     * AdminCategoryController constructor.
     *
     * @param EntityManagerInterface    $entityManager
     * @param CategoryRepository        $categoryRepository
     */
    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        parent::__construct($entityManager);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Pagination and retrieve all categories.
     * 
     * @Route("/{page<\d+>?1}", name="admin_category_index")
     *
     * @param int $page
     * @param PaginationService $pagination
     * 
     * @return Response
     */
    public function index($page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Category::class)
            ->setLimit(PageConstant::DEFAULT_NUMBER_PER_PAGE)
            ->setPage($page);

        return $this->render('admin/category/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Create one category.
     * 
     * @Route("/new", name="admin_category_new", methods={"GET", "POST"})
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setAuthor($this->getUser());
            $this->save($category);

            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "La catégorie {$category->getTitle()} a bien été créee !"
            );

            return $this->redirectToRoute('admin_category_index');
        }
        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Modify a category.
     * 
     * @Route("/{id}/edit", name="admin_category_edit", methods={"GET", "POST"})
     *
     * @param Category $category
     * @param Request $request
     * 
     * @return Response
     */
    public function edit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setAuthor($this->getUser());
            $this->save($category);

            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "La catégorie {$category->getTitle()} a bien été modifiée !"
            );

            return $this->redirectToRoute('admin_category_index');
        }
        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Remove category.
     * 
     * @Route("/{id}/delete", name="admin_category_delete")
     *
     * @param Category $category
     * 
     * @return Response
     */
    public function delete(Category $category): Response
    {
        if ($this->remove($category)) {
            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "La catégorie {$category->getTitle()} a bien été supprimée !"
            );
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }
        return $this->redirectToRoute('admin_category_index');
    }
}
