<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Config\Definition\Builder;

use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;


/**
 * This class provides a fluent interface for building a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class NodeBuilder extends BaseNodeBuilder
{
    /**
     * NodeBuilder constructor.
     */
    public function __construct()
    {

        $this->nodeMapping = [
            'variable' => VariableNodeDefinition::class,
            'scalar' => ScalarNodeDefinition::class,
            'boolean' => BooleanNodeDefinition::class,
            'integer' => IntegerNodeDefinition::class,
            'float' => FloatNodeDefinition::class,
            'array' => ArrayNodeDefinition::class,
            'enum' => EnumNodeDefinition::class,
        ];
    }

    /**
     * @param string $name
     * @return ArrayNodeDefinition
     */
    public function resourcesNode(string $name = BE::CONFIG_KEY_RESOURCES)
    {
        $node = new ArrayNodeDefinition($name);
        $this->append($node);
        return $node;
    }

    /**
     * @param string $resourceName
     * @param string $entityInterface
     * @param string $factoryClass
     * @param string $repositoryClass
     * @param string $managerClass
     * @param string $typeClass
     * @return ArrayNodeDefinition
     */
    public function resourceNode(
        string $resourceName,
        string $entityInterface,
        string $factoryClass,
        string $repositoryClass,
        string $managerClass,
        string $typeClass,
        ?string $voterSubjectClass = null
    ) {
        $node = new ArrayNodeDefinition($resourceName);
        $node->addResourceNode(
            false,
            true,
            $entityInterface,
            $factoryClass,
            $repositoryClass,
            $managerClass,
            $typeClass,
            null,
            null,
            $voterSubjectClass
        );
        
        $this->append($node);

        return $node;
    }

    /**
     * @param string $resourceName
     * @param string $entityInterface
     * @param string $factoryClass
     * @param string $repositoryClass
     * @param string $managerClass
     * @param string $typeClass
     * @param string $translationEntityInterface
     * @param string $translationTypeClass
     * @return ArrayNodeDefinition
     */
    public function translatedResourceNode(
        string $resourceName,
        string $entityInterface,
        string $factoryClass,
        string $repositoryClass,
        string $managerClass,
        string $typeClass,
        string $translationEntityInterface,
        string $translationTypeClass,
        ?string $voterSubjectClass = null
    ): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition($resourceName);
        $node->addResourceNode(
            true,
            true,
            $entityInterface,
            $factoryClass,
            $repositoryClass,
            $managerClass,
            $typeClass,
            $translationEntityInterface,
            $translationTypeClass,
            $voterSubjectClass
        );

        $this->append($node);

        return $node;
    }

    /**
     * @param string $traslationDomain
     * @return ScalarNodeDefinition
     */
    public function translationDomainScalar(string $traslationDomain)
    {
        $node = new ScalarNodeDefinition(BE::CONFIG_KEY_TRANSLATION_DOMAIN);
        $node->defaultValue($traslationDomain);

        $this->append($node);

        return $node;
    }

    /**
     * @param string $class
     * @return ScalarNodeDefinition
     * @throws \ReflectionException
     */
    public function bundleTranslationDomainScalar(string $class)
    {
        $node = new ScalarNodeDefinition(BE::CONFIG_KEY_TRANSLATION_DOMAIN);
        $node->defaultValue((new \ReflectionClass($class))->getShortName());

        $this->append($node);

        return $node;
    }
}
