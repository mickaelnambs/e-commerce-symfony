<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Form\ProductType;
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
    public function index($page, PaginationService $pagination)
    {
        $pagination->setEntityClass(Product::class)
            ->setLimit(5)
            ->setPage($page);

        return $this->render('admin/product/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Edit product.
     * 
     * @Route("/{id}/edit", name="admin_product_edit", methods={"GET", "POST"})
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function edit(Product $product, Request $request)
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('images')->getData();
            $product->setAuthor($this->getUser())
                    ->setIsModified(true);

            $this->uploadFiles($file, $product);
            $this->save($product);

            $this->addFlash(
                'success',
                "Le produit <strong>{$product->getMark()}</strong> a bien été modifié !"
            );

            return $this->redirectToRoute('admin_product_index');
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
    public function delete(Product $product)
    {
        if ($product) {
            $this->remove($product);
            $this->addFlash(
                'success',
                "Le produit <strong>{$product->getMark()}</strong> a bien été supprimé !"
            );
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
