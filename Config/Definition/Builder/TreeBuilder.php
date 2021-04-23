<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Config\Definition\Builder;

use Symfony\Component\Config\Definition\Builder\TreeBuilder as BaseTreeBuilder;

/**
 * Class TreeBuilder
 * @package LSB\UtilityBundle\Config\Definition\Builder
 */
class TreeBuilder extends BaseTreeBuilder
{

    /**
     * TreeBuilder constructor.
     * @param string $name
     * @param string $type
     * @param NodeBuilder|null $builder
     */
    public function __construct(string $name, string $type = 'array', NodeBuilder $builder = null)
    {
        $builder = $builder ?? new NodeBuilder();
        $this->root = $builder->node($name, $type)->setParent($this);
    }

}