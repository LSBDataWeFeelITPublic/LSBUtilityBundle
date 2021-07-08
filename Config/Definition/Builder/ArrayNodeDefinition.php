<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Config\Definition\Builder;

use LSB\ProductBundle\Factory\ProductFactory;
use LSB\ProductBundle\Form\ProductTranslationType;
use LSB\ProductBundle\Form\ProductType;
use LSB\ProductBundle\Manager\ProductManager;
use LSB\ProductBundle\Repository\ProductRepository;
use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition as BaseArrayNodeDefinition;

/**
 * Class ArrayNodeDefinition
 * @package LSB\UtilityBundle\DependencyInjection\Builder
 */
class ArrayNodeDefinition extends BaseArrayNodeDefinition
{
    protected function getNodeBuilder()
    {
        if (null === $this->nodeBuilder) {
            $this->nodeBuilder = new NodeBuilder();
        }

        return $this->nodeBuilder->setParent($this);
    }

    /**
     * @param string $entityClass
     * @param string $entityInterface
     * @param string $factoryClass
     * @param string $repositoryClass
     * @param string $managerClass
     * @param string $formTypeClass
     * @param string|null $voterSubjectClass
     * @return $this
     */
    public function addClassesNode(
        string $entityClass,
        string $entityInterface,
        string $factoryClass,
        string $repositoryClass,
        string $managerClass,
        string $formTypeClass,
        string $voterSubjectClass = null
    ): BaseArrayNodeDefinition {
        $this
            ->children()
            ->arrayNode(BE::CONFIG_KEY_CLASSES)
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode(BE::CONFIG_KEY_ENTITY)->defaultValue($entityClass)->end()
            ->scalarNode(BE::CONFIG_KEY_INTERFACE)->defaultValue($entityInterface)->end()
            ->scalarNode(BE::CONFIG_KEY_FACTORY)->defaultValue($factoryClass)->end()
            ->scalarNode(BE::CONFIG_KEY_REPOSITORY)->defaultValue($repositoryClass)->end()
            ->scalarNode(BE::CONFIG_KEY_MANAGER)->defaultValue($managerClass)->end()
            ->scalarNode(BE::CONFIG_KEY_FORM)->defaultValue($formTypeClass)->end()
            ->scalarNode(BE::CONFIG_KEY_VOTER_SUBJECT)->defaultValue($voterSubjectClass)->end()
            ->end()
            ->end()
            ->end();

        return $this;
    }

    /**
     * @param string $translationEntityClass
     * @param string $translationEntityInterface
     * @param string $translationFormTypeClass
     * @return $this
     */
    public function addTranslationNode(
        string $translationEntityClass,
        string $translationEntityInterface,
        string $translationFormTypeClass
    ): BaseArrayNodeDefinition {
        $this
            ->children()
            ->arrayNode(BE::CONFIG_KEY_TRANSLATION)
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode(BE::CONFIG_KEY_ENTITY)->defaultValue($translationEntityClass)->end()
            ->scalarNode(BE::CONFIG_KEY_INTERFACE)->defaultValue($translationEntityInterface)->end()
            ->scalarNode(BE::CONFIG_KEY_FACTORY)->end()
            ->scalarNode(BE::CONFIG_KEY_REPOSITORY)->end()
            ->scalarNode(BE::CONFIG_KEY_FORM)->defaultValue($translationFormTypeClass)->end()
            ->end()
            ->end()
            ->end();

        return $this;
    }

    /**
     * @param bool $setDefaults
     * @param bool $addTranslation
     * @param string $factoryClass
     * @param string $repositoryClass
     * @param string $managerClass
     * @param string $typeClass
     * @param string|null $translationTypeClass
     * @param string|null $voterSubjectClass
     * @return $this
     */
    public function addContextNode(
        bool $setDefaults,
        bool $addTranslation,
        string $factoryClass,
        string $repositoryClass,
        string $managerClass,
        string $typeClass,
        ?string $translationTypeClass = null,
        ?string $voterSubjectClass = null
    ) {
        $builder = $this
            ->children()
                ->arrayNode(BE::CONFIG_KEY_CONTEXT)
                ->arrayPrototype()
                    ->children()
                        ->arrayNode(BE::CONFIG_KEY_CLASSES)
                            ->children()
                            ->scalarNode(BE::CONFIG_KEY_FACTORY)->defaultValue($setDefaults ? $factoryClass : null)->end()
                            ->scalarNode(BE::CONFIG_KEY_REPOSITORY)->defaultValue($setDefaults ? $repositoryClass : null)->end()
                            ->scalarNode(BE::CONFIG_KEY_MANAGER)->defaultValue($setDefaults ? $managerClass : null)->end()
                            ->scalarNode(BE::CONFIG_KEY_FORM)->defaultValue($setDefaults ? $typeClass : null)->end()
                            ->scalarNode(BE::CONFIG_KEY_VOTER_SUBJECT)->defaultValue($setDefaults ? $voterSubjectClass : null)->end()
                        ->end()
                    ->end();


        if ($addTranslation) {
            $builder
                ->arrayNode(BE::CONFIG_KEY_TRANSLATION)
                ->children()
                ->scalarNode(BE::CONFIG_KEY_FACTORY)->end()
                ->scalarNode(BE::CONFIG_KEY_REPOSITORY)->end()
                ->scalarNode(BE::CONFIG_KEY_FORM)->defaultValue($setDefaults ? $translationTypeClass : null)->end()
                ->end()
                ->end();
        }

        $builder
            ->end()
        ->end();

        return $this;
    }

    /**
     * @param bool $addTranslationNode
     * @param bool $addContextNode
     * @param string $entityClass
     * @param string $entityInterface
     * @param string $factoryClass
     * @param string $repositoryClass
     * @param string $managerClass
     * @param string $typeClass
     * @param string|null $translationEntityClass
     * @param string|null $translationEntityInterface
     * @param string|null $translationTypeClass
     * @param string|null $voterSubjectClass
     * @return $this
     */
    public function addResourceNode(
        bool $addTranslationNode,
        bool $addContextNode,
        string $entityClass,
        string $entityInterface,
        string $factoryClass,
        string $repositoryClass,
        string $managerClass,
        string $typeClass,
        ?string $translationEntityClass,
        ?string $translationEntityInterface,
        ?string $translationTypeClass,
        ?string $voterSubjectClass
    ) {
        $tree = $this
            ->addClassesNode(
                $entityClass,
                $entityInterface,
                $factoryClass,
                $repositoryClass,
                $managerClass,
                $typeClass,
                $voterSubjectClass
            );

        if ($addTranslationNode) {
            $tree->addTranslationNode(
                $translationEntityClass,
                $translationEntityInterface,
                $translationTypeClass
            );
        }


        if ($addContextNode) {
            $tree->addContextNode(
                false,
                $addTranslationNode,
                $factoryClass,
                $repositoryClass,
                $managerClass,
                $typeClass,
                $translationTypeClass,
                $voterSubjectClass
            );
        }


        $tree->end();

        return $this;
    }
}
