<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Compiler;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class BaseResolveEntitiesPass
 * @package LSB\UtilityBundle\DependencyInjection\Compiler
 */
abstract class BaseResolveEntitiesPass implements CompilerPassInterface
{

    const CONFIG_KEY_CONFIG = 'config';
    const CONFIG_KEY_CLASSES = 'classes';
    const CONFIG_KEY_ENTITY = 'entity';
    const CONFIG_KEY_INTERFACE = 'interface';
    const CONFIG_KEY_RESOURCES = 'resources';
    const DOCTRINE_RESOLVE_TARGET_ENTITY_LISTENER = 'doctrine.orm.listeners.resolve_target_entity';

    /**
     * @param ContainerBuilder $container
     * @param string $prefix
     * @throws \Exception
     */
    protected function processResources(ContainerBuilder $container, string $prefix)
    {

        $resourcesParameterName = $prefix.'.'.static::CONFIG_KEY_CONFIG.'.'.static::CONFIG_KEY_RESOURCES;

        if (!$container->hasParameter($resourcesParameterName)) {
//            throw new \InvalidArgumentException("Missing $prefix resource parameter: $resourcesParameterName");
            return;
        }

        $resources = $container->getParameter($resourcesParameterName);

        //Resolve entities
        $resolveTargetEntityListener = $container->findDefinition(static::DOCTRINE_RESOLVE_TARGET_ENTITY_LISTENER);

        if (!$resolveTargetEntityListener) {
            throw new \Exception('Missing doctrine resolve target entity listener');
        }

        /**
         * @var array $data
         */
        foreach ($resources as $resource => $data) {
            $entityClass = $data[static::CONFIG_KEY_CLASSES][static::CONFIG_KEY_ENTITY] ?? null;
            $interfaceClass = $data[static::CONFIG_KEY_CLASSES][static::CONFIG_KEY_INTERFACE] ?? null;
            $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [$interfaceClass, $entityClass, []]);
        }

        $resolveTargetEntityListenerClass = $container->getParameterBag()->resolveValue($resolveTargetEntityListener->getClass());

        if (is_a($resolveTargetEntityListenerClass, EventSubscriber::class, true)) {
            if (!$resolveTargetEntityListener->hasTag('doctrine.event_subscriber')) {
                $resolveTargetEntityListener->addTag('doctrine.event_subscriber');
            }
        } elseif (!$resolveTargetEntityListener->hasTag('doctrine.event_listener')) {
            $resolveTargetEntityListener->addTag('doctrine.event_listener', ['event' => 'loadClassMetadata']);
        }
    }
}
