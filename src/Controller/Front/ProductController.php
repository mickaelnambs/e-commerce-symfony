<?php

namespace App\Controller\Front;

use App\Entity\Contact;
use App\Entity\Product;
use App\Form\ContactType;
use App\Form\ProductType;
use App\Service\CartService;
use App\Entity\ProductSearch;
use App\Constant\PageConstant;
use App\Form\ProductSearchType;
use App\Constant\MessageConstant;
use App\Controller\BaseController;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ProductController.
 * 
 * @Route("/products")
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 * 
 */
class ProductController extends BaseController
{
    /** @var ProductRepository */
    private ProductRepository $productRepository;

    /**
     * ProductController constructeur.
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
     * Pagination des produits et filtre de recherche.
     * 
     * @Route("/", name="product_index")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * 
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $search = new ProductSearch();
        $form = $this->createForm(ProductSearchType::class, $search);
        $form->handleRequest($request);

        $products = $paginator->paginate(
            $this->productRepository->findAllVisibleQuery($search),
            $request->query->getInt('page', PageConstant::DEFAULT_PAGE), 
            PageConstant::DEFAULT_NUMBER_PER_PAGE
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }


    /**
     * Créer un nouveau produit.
     * 
     * @Route("/new", name="product_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
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
            $product->setAuthor($this->getUser());

            $this->uploadFiles($file, $product);
            $this->save($product);

            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Le produit <strong>{$product->getMark()}</strong> a bien été crée !"
            );

            return $this->redirectToRoute('product_index');
        }
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Affiche le detail d'un produit.
     * 
     * @Route("/{slug}-{id}", name="product_show", methods={"GET"}, requirements={"slug": "[a-z0-9\-]*"})
     *
     * @param Product $product
     * @param string $slug
     * 
     * @return Response
     */
    public function show(Product $product, string $slug): Response
    {
        if ($product->getSlug() !== $slug) {
            return $this->redirectToRoute('product_show', [
                'slug' => $product->getSlug(),
                'id' => $product->getId()
            ]);
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Modifier un produit.
     * 
     * @Route("/{id}/edit", name="product_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER') and user == product.getAuthor()", message="Vous n'avez pas le droit de modifier cette ressource")
     *
     * @param Request $request
     * @param Product $product
     * 
     * @return Response
     */
    public function edit(Product $product, Request $request): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('images')->getData();
            $product->setAuthor($this->getUser());

            $this->uploadFiles($file, $product);
            $this->save($product);

            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Le produit <strong>{$product->getMark()}</strong> a bien été modifié !"
            );

            return $this->redirectToRoute('product_index');
        }
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Supprimer un produit.
     * 
     * @Route("/{id}/delete", name="product_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER') and user == product.getAuthor()", message="Vous n'avez pas le droit de supprimer cette ressource")
     *
     * @param Product $product
     * 
     * @return Response
     */
    public function delete(Product $product): Response
    {
        $this->remove($product);

        $this->addFlash(
            MessageConstant::SUCCESS_TYPE,
            "Le produit <strong>{$product->getMark()}</strong> a bien été supprimé !"
        );
        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/cart", name="product_cart")
     * 
     * @param CartService $cartService
     * 
     * @return Response
     */
    public function cart(CartService $cartService): Response
    {
        return $this->render('product/cart/cart.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal()
        ]);
    }

    /**
     * @Route("/cart/{id}", name="product_cart_add")
     * 
     * @param integer $id
     * @param CartService $cartService
     * 
     * @return Response
     */
    public function addToCart(int $id, CartService $cartService): Response
    {
        if (null !== $id) {
            $cartService->add($id);
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Error'], 400);
        }
        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/cart/{id}/delete", name="product_cart_delete")
     *
     * @param integer $id
     * @param CartService $cartService
     * 
     * @return Response
     */
    public function removeToCart(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);
        return $this->redirectToRoute('product_cart');
    }
}
