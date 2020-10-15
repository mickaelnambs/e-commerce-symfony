<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PaginationService.
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class PaginationService
{
    /** @var string */
    private string $entityClass;

    /** @var integer */
    private int $limit = 10;

    /** @var integer */
    private int $currentPage = 1;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var Twig\Environment */
    private $twig;

    /** @var string */
    private string $route;

    /** @var string */
    private string $templatePath;

    /**
     * PaginationService constructeur.
     *
     * @param EntityManagerInterface    $entityManager
     * @param Environment               $twig
     * @param RequestStack              $request
     * @param string                    $templatePath
     */
    public function __construct(EntityManagerInterface $entityManager, Environment $twig, RequestStack $request, string $templatePath)
    {
        $this->route            = $request->getCurrentRequest()->attributes->get('_route');
        $this->entityManager    = $entityManager;
        $this->twig             = $twig;
        $this->templatePath     = $templatePath;
    }

    /**
     * Permet d'afficher le rendu de la navigation au sein d'un template twig.
     *
     * @return void
     */
    public function display()
    {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ]);
    }

    /**
     * Permet de récupérer le nombre de pages qui existent sur une entité particulière.
     * 
     * @throws Exception si la propriété $entityClass n'est pas configurée.
     * 
     * @return int
     */
    public function getPages()
    {
        if (empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilisez la méthode setEntityClass() de votre objet PaginationService !");
        }

        $total = count($this->entityManager
            ->getRepository($this->entityClass)
            ->findAll());

        return ceil($total / $this->limit);
    }

    /**
     * Permet de récupérer les données paginées pour une entité spécifique.
     * 
     * @throws Exception si la propriété $entityClass n'est pas définie.
     *
     * @return array
     */
    public function getData()
    {
        if (empty($this->entityClass)) {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilisez la méthode setEntityClass() de votre objet PaginationService !");
        }
        $offset = $this->currentPage * $this->limit - $this->limit;

        return $this->entityManager
            ->getRepository($this->entityClass)
            ->findBy([], [], $this->limit, $offset);
    }

    /**
     * Permet de spécifier la page que l'on souhaite afficher.
     *
     * @param int $page
     * @return self
     */
    public function setPage(int $page): self
    {
        $this->currentPage = $page;

        return $this;
    }

    /**
     * Permet de récupérer la page qui est actuellement affichée.
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Permet de spécifier le nombre d'enregistrements que l'on souhaite obtenir.
     *
     * @param int $limit
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Permet de récupérer le nombre d'enregistrements qui seront renvoyés.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Permet de spécifier l'entité sur laquelle on souhaite paginer.
     *
     * @param string $entityClass
     * @return self
     */
    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Permet de récupérer l'entité sur laquelle on est en train de paginer.
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
    
    /**
     * Permet de choisir un template de pagination.
     *
     * @param string $templatePath
     * @return self
     */
    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * Permet de récupérer le templatePath actuellement utilisé.
     *
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * Permet de changer la route par défaut pour les liens de la navigation.
     *
     * @param string $route
     * @return self
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Permet de récupérer le nom de la route qui sera utilisé sur les liens de la navigation.
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }
}