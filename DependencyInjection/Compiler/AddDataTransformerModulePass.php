<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Compiler;

use LSB\OrderBundle\Service\CartModuleService;
use LSB\UtilityBundle\DTO\DataTransformer\DataTransformerInterface;
use LSB\UtilityBundle\DTO\DataTransformer\DataTransformerModuleInventory;
use LSB\UtilityBundle\ModuleInventory\BaseModuleInventory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddDataTransformerModulePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DataTransformerModuleInventory::class)) {
            return;
        }

        $def = $container->findDefinition(DataTransformerModuleInventory::class);

        foreach ($container->findTaggedServiceIds(DataTransformerInterface::TAG) as $id => $attrs) {
            $def->addMethodCall(BaseModuleInventory::ADD_MODULE_METHOD, [new Reference($id), $attrs]);
        }
    }
}
