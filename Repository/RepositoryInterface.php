<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

/**
 * Interface RepositoryInterface
 * @package LSB\UtilityBundle\Factory
 */
interface RepositoryInterface extends ServiceEntityRepositoryInterface, PaginationInterface
{

}