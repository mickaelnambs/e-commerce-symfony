<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Form\ProductType;
use App\Constant\PageConstant;
use App\Constant\MessageConstant;
use App\Controller\BaseController;
use App\Service\PaginationService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AdminProductController.
 * 
 * @Route("/admin/products")
 * @IsGranted("ROLE_ADMIN")
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class AdminProductController extends BaseController
{
    /** @var ProductRepository */
    private ProductRepository $productRepository;

    /**
     * AdminProductController constructor.
     *
     * @param EntityManagerInterface    $entityManager
     * @param ProductRepository         $productRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        parent::__construct($entityManager);
        $this->productRepository = $productRepository;
    }

    /**
     * Pagination and retrieve all products
     * 
     * @Route("/{page<\d+>?1}", name="admin_product_index")
     *
     * @param int $page
     * @param PaginationService $pagination
     * 
     * @return Response
     */
    public function index($page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Product::class)
            ->setLimit(PageConstant::DEFAULT_NUMBER_PER_PAGE)
            ->setPage($page);

        return $this->render('admin/product/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Créer un nouveau produit.
     * 
     * @Route("/new", name="admin_product_new", methods={"POST","GET"})
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('images')->getData();
            $this->uploadFiles($file, $product);
            $product->setAuthor($this->getUser());

            if ($this->save($product)) {
                $this->addFlash(
                    MessageConstant::SUCCESS_TYPE,
                    "Le produit {$product->getMark()} a bien été crée !"
                );
                return $this->redirectToRoute('admin_product_index');
            }
            $this->addFlash(
                MessageConstant::ERROR_TYPE,
                "Il y a une erreur pendant la création"
            );
            return $this->redirectToRoute('admin_product_new');
        }
        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit product.
     * 
     * @Route("/{id}/edit", name="admin_product_edit", methods={"POST","GET"})
     * 
     * @param Product $product
     * @param Request $request
     * 
     * @return Response
     */
    public function edit(Product $product, Request $request): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('images')->getData();
            $this->uploadFiles($file, $product);
            $product->setAuthor($this->getUser())
                ->setIsModified(true);

            if ($this->save($product)) {
                $this->addFlash(
                    MessageConstant::SUCCESS_TYPE,
                    "Le produit {$product->getMark()} a bien été modifié !"
                );
                return $this->redirectToRoute('admin_product_index');
            }
            $this->addFlash(
                MessageConstant::ERROR_TYPE,
                "Il y a une erreur pendant la modification !"
            );
            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }
        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Remove product.
     * 
     * @Route("/{id}/delete", name="admin_product_delete")
     *
     * @param Product $product
     * 
     * @return Response
     */
    public function delete(Product $product): Response
    {
        if ($this->remove($product)) {
            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Le produit {$product->getMark()} a bien été supprimé !"
            );
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }
        return $this->redirectToRoute('admin_product_index');
    }
}
