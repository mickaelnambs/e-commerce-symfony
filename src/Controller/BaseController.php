<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class BaseController.
 *                                                                  
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class BaseController extends AbstractController
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;

    /**
     * BaseController constructeur.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param object $object
     * 
     * @return bool
     */
    public function save(object $object): bool
    {
        try {
            if (!$object->getId()) {
                $this->entityManager->persist($object);
            }
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param object $object
     * 
     * @return bool
     */
    public function remove(object $object): bool
    {
        try {
            if ($object) {
                $this->entityManager->remove($object);
            }
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param File $file
     * @param object $object
     * 
     * @return object
     */
    public function uploadFile(File $file, object $object): object
    {
        $filename = bin2hex(random_bytes(6)) . '.' . $file->guessExtension();
        $file->move($this->getParameter('image_directory'), $filename);
        $object->setImage($filename);

        return $object;
    }

    /**
     * @param array $files
     * @param object $object
     * 
     * @return object
     */
    public function uploadFiles(array $files, object $object): object
    {
        foreach ($files as $file) {
            $filename = bin2hex(random_bytes(6)) . '.' . $file->guessExtension();
            $file->move($this->getParameter('image_directory'), $filename);

            $image = new Image();
            $image->setName($filename);
            $object->addImage($image);
        }
        return $object;
    }
}
