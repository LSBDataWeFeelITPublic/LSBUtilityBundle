<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Container;

use LSB\UtilityBundle\Factory\FactoryInterface;
use LSB\UtilityBundle\Form\BaseEntityType;
use LSB\UtilityBundle\Manager\ManagerInterface;
use LSB\UtilityBundle\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Trait TagServiceTrait
 * @package LSB\UtilityBundle\Kernel
 */
trait TagServiceTrait
{
    /**
     * @param ContainerBuilder $container
     */
    protected function addTags(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ManagerInterface::class)->addTag(ServiceTagInterface::TAG_MANAGER);
        $container->registerForAutoconfiguration(FactoryInterface::class)->addTag(ServiceTagInterface::TAG_FACTORY);
        $container->registerForAutoconfiguration(BaseEntityType::class)->addTag(ServiceTagInterface::TAG_FORM);
        $container->registerForAutoconfiguration(RepositoryInterface::class)->addTag(ServiceTagInterface::TAG_REPOSITORY);
    }
}