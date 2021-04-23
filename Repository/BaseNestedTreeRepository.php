<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use LSB\UtilityBundle\Application\AppCodeTrait;

/**
 * Class BaseNestedTreeRepository
 * @package LSB\UtilityBundle\Repository
 */
abstract class BaseNestedTreeRepository extends NestedTreeRepository
{
    use AppCodeTrait;
    /**
     * CategoryRepository constructor.
     * @param EntityManagerInterface $em
     * @param ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * @inheritDoc
     */
    public function childrenQueryBuilder($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false)
    {
        $qb = parent::childrenQueryBuilder($node, $direct, $sortByField, $direction);

        $qb->leftJoin('node.translations', 'ts');
        $qb->addSelect('ts');

        return $qb;
    }
}
