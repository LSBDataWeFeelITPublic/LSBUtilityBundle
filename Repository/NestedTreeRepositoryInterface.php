<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Repository;

use Gedmo\Tree\RepositoryInterface as TreeInterface;

/**
 * Interface NestedTreeRepositoryInterfaceInterface
 * @package LSB\UtilityBundle\Repository
 */
interface NestedTreeRepositoryInterface extends TreeInterface
{
    /**
     * @param null $node
     * @param false $direct
     * @param array $options
     * @param false $includeNode
     * @return array|string
     */
    public function childrenHierarchy($node = null, $direct = false, array $options = [], $includeNode = false);
}