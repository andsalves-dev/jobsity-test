<?php

namespace App\Traits;

use Doctrine\ORM\EntityManagerInterface;

trait EntityManagerAwareTrait {
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @required
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void {
        $this->entityManager = $entityManager;
    }

    /** @return EntityManagerInterface */
    public function getEntityManager(): EntityManagerInterface {
        return $this->entityManager;
    }
}