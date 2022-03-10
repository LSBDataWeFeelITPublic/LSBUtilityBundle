<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Service;

use App\Entity\Product\AppProduct;

class ManagerContainer implements ManagerContainerInterface
{
    /**
     * @var array
     */
    protected array $managers = [];

    public function addManager(\LSB\UtilityBundle\Manager\ManagerInterface $manager): void
    {
        if (!array_key_exists($manager->getResourceEntityClass(), $this->managers)) {
            $this->managers[$manager->getResourceEntityClass()][$manager::class] = $manager;
        }
    }

    public function getByEntityClass(string $entityClass): ?\LSB\UtilityBundle\Manager\ManagerInterface
    {
        if (array_key_exists($entityClass, $this->managers)) {
            return $this->managers[$entityClass];
        }

        return null;
    }

    public function getByManagerClass(string $managerClass): ?\LSB\UtilityBundle\Manager\ManagerInterface
    {
        foreach($this->managers as $managers) {
            if (array_key_exists($managerClass, $managers)) {
                return $managers[$managerClass];
            }
        }

        return null;
    }
}