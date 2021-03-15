<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface PaginationInterface
 * @package LSB\UtilityBundle\Repository
 */
interface PaginationInterface
{
    const DEFAULT_ALIAS = 'e';

    /**
     * @return QueryBuilder
     */
    public function getPaginationQueryBuilder(string $alias = self::DEFAULT_ALIAS): QueryBuilder;
}