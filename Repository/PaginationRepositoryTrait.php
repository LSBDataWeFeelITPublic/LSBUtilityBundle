<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * Trait PaginationRepositoryTrait
 * @package LSB\UtilityBundle\Repository
 */
trait PaginationRepositoryTrait
{
    /**
     * @return QueryBuilder
     */
    public function getPaginationQueryBuilder(string $alias = PaginationInterface::DEFAULT_ALIAS): QueryBuilder
    {
        return $this->createQueryBuilder($alias);
    }
}