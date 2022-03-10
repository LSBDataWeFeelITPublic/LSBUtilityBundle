<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Compiler;

use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Service\ManagerContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddManagerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ManagerContainerInterface::class)) {
            return;
        }

        $def = $container->findDefinition(ManagerContainerInterface::class);

        foreach ($container->findTaggedServiceIds(ManagerInterface::MANAGER_TAG) as $id => $attrs) {
            $def->addMethodCall('addManager', [new Reference($id), $attrs]);
        }
    }
}
