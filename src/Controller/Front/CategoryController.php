<?php

namespace App\Controller\Front;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Controller\BaseController;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Class CategoryController.
 * 
 * @Route("/categories")
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class CategoryController extends BaseController
{
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;

    /**
     * CategoryController constuctor.
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
     * Retrieve all categories.
     * 
     * @Route("/", name="category_index", methods={"GET"})
     * 
     * @return Response
     */
    public function index()
    {
        return $this->render('category/index.html.twig', [
            'categories' => $this->categoryRepository->findAll()
        ]);
    }

    /**
     * Create category.
     * 
     * @Route("/new", name="category_new", methods={"GET", "POST"})
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function new(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setAuthor($this->getUser());

            $this->save($category);

            $this->addFlash(
                'success',
                "La catégorie <strong>{$category->getTitle()}</strong> a bien été créee !"
            );
            return $this->redirectToRoute('category_index');
        }
        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Replace category.
     * 
     * @Route("/{id}/edit", name="category_edit", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Category $category
     * 
     * @return Response
     */
    public function edit(Category $category, Request $request)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setAuthor($this->getUser());

            $this->save($category);

            $this->addFlash(
                'success',
                "La catégorie <strong>{$category->getTitle()}</strong> a bien été modifiée !"
            );
            return $this->redirectToRoute('category_index');
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * Remove category.
     * 
     * @Route("/{id}/delete", name="category_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER') and user == category.getAuthor()", message="Vous n'avez pas le droit d'accéder à cette ressource")
     *
     * @param Category $category
     * 
     * @return Response
     */
    public function delete(Category $category)
    {
        $this->remove($category);

        $this->addFlash(
            'success',
            "La catégorie <strong>{$category->getTitle()}</strong> a bien été supprimée !"
        );
        $this->redirectToRoute('category_index');
    }
}
