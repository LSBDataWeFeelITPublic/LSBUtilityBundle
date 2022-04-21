<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Service;

use LSB\UtilityBundle\Manager\ManagerInterface;

interface ManagerContainerInterface
{
    public function getByManagerClass(string $managerClass): ?ManagerInterface;
}