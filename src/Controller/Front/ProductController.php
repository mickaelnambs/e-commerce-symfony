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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

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
     * @Route("/", name="product_index", methods={"POST","GET"})
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
     * Permet d'afficher le detail d'un produit et aussi faire l'envoi d'email.
     * 
     * @Route("/{slug}-{id}", name="product_show", methods={"POST","GET"}, requirements={"slug": "[a-z0-9\-]*"})
     *
     * @param Product $product
     * @param Request $request
     * @param MailerInterface $mailer
     * @param string $slug
     * 
     * @return Response
     */
    public function show(Product $product, Request $request, MailerInterface $mailer, string $slug): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new TemplatedEmail())
                ->from($contact->getEmail())
                ->to($product->getAuthor()->getEmail())
                ->subject("Contact au sujet de votre produit " . $product->getMark() . "!")
                ->htmlTemplate('email/contact_product.html.twig')
                ->context([
                    'product' => $product,
                    'mail' => $contact->getEmail(),
                    'message' => $contact->getMessage()
                ]);
            $mailer->send($email);
            $this->addFlash(
                MessageConstant::SUCCESS_TYPE,
                "Votre email a bien été envoyé !"
            );
            return $this->redirectToRoute('product_show', [
                'slug' => $product->getSlug(),
                'id' => $product->getId()
            ]);
        }

        if ($product->getSlug() !== $slug) {
            return $this->redirectToRoute('product_show', [
                'slug' => $product->getSlug(),
                'id' => $product->getId()
            ]);
        }
        return $this->render('product/show.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    /**
     * @Route("/cart", name="product_cart", methods={"POST","GET"})
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
     * @Route("/cart/{id}", name="product_cart_add", methods={"POST","GET"})
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
