<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class BaseManagerResourcePass
 * @package LSB\UtilityBundle\DependencyInjection\Compiler
 */
abstract class BaseManagerResourcePass implements CompilerPassInterface
{
    const CONFIG_KEY_CONFIG = 'config';
    const CONFIG_KEY_CLASSES = 'classes';
    const CONFIG_KEY_FACTORY = 'factory';
    const CONFIG_KEY_MANAGER = 'manager';
    const CONFIG_KEY_ENTITY = 'entity';
    const CONFIG_KEY_REPOSITORY = 'repository';
    const CONFIG_KEY_RESOURCES = 'resources';

    const TAG_FORM_TYPE = 'form.type';

    /**
     * @param ContainerBuilder $container
     * @param string $prefix
     * @param string $factoryArg
     * @param string $repositoryArg
     * @param string $formArg
     * @param string $translationDomainArg
     * @param string $classNameArg
     * @throws \ReflectionException
     */
    protected function processResources(
        ContainerBuilder $container,
        string $prefix,
        string $factoryArg = '$factory',
        string $repositoryArg = '$repository',
        string $formArg = '$form',
        string $translationDomainArg = '$translationDomain',
        string $classNameArg = '$className',
        bool $useMethodCalls = false
    ): void {

        $resourcesParameterName = $prefix . '.' . BE::CONFIG_KEY_CONFIG . '.' . BE::CONFIG_KEY_RESOURCES;
        $translationDomainParameterName = $prefix . '.' . BE::CONFIG_KEY_CONFIG . '.' . BE::CONFIG_KEY_TRANSLATION_DOMAIN;

        if (!$container->hasParameter($resourcesParameterName)) {
//            throw new \InvalidArgumentException("Missing $prefix resource parameter: $resourcesParameterName");
            return;
        }

        $resources = $container->getParameter($resourcesParameterName);
        $translationDomain = $container->getParameter($translationDomainParameterName);

        /**
         * @var array $data
         */
        foreach ($resources as $resource => $data) {
            $managerClass = $data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_MANAGER] ?? null;
            $factoryClass = $data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FACTORY] ?? null;
            $formClass = $data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM] ?? null;
            $translationFormClass = $data[BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_FORM] ?? null;
            $repositoryClass = $data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_REPOSITORY] ?? null;
            $entityClass = $data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY] ?? null;


            if (!$managerClass || !$container->has($managerClass)) {
                throw new \Exception('Missing service manager definition for resource: ' . $resource);
            }

            $managerDef = $container->findDefinition($managerClass);

            //Factory definition
            $factoryDef = $factoryClass && $container->hasDefinition($factoryClass) ? $container->findDefinition($factoryClass) : null;

            if ($factoryDef) {
                $managerDef->setArgument($factoryArg, $factoryDef);
                $factoryDef->setArgument($classNameArg, $entityClass);
            }

            //Repository definition
            $repositoryDef = $repositoryClass && $container->hasDefinition($repositoryClass) ? $container->findDefinition($repositoryClass) : null;

            if ($repositoryDef) {
                $managerDef->setArgument($repositoryArg, $repositoryDef);
            }

            //Form definitions
            $formDef = $formClass && $container->hasDefinition($formClass) ? $container->findDefinition($formClass) : null;

            if ($formDef) {
                $managerDef->setArgument($formArg, $formDef);
                $this->setFormArguments($formDef, $useMethodCalls, $classNameArg, $translationDomainArg, $entityClass, $translationDomain);
            }

            $translationFormDef = $translationFormClass && $container->hasDefinition($translationFormClass) ? $container->findDefinition($translationFormClass) : null;

            if ($translationFormClass) {
                $this->setFormArguments($translationFormDef, $useMethodCalls, $classNameArg, $translationDomainArg, $entityClass, $translationDomain);
            }

            $parentsClasses = class_parents($managerClass);

            /**
             * @var string $parentsClass
             */
            foreach ($parentsClasses as $parentsClass) {
                $parentsClassReflection = new \ReflectionClass($parentsClass);
                if ($parentsClassReflection->isAbstract()) {
                    continue;
                }

                if ($container->hasDefinition($parentsClass)) {
                    $defaultManagerDef = $container->getDefinition($parentsClass);
                    if ($factoryDef) {
                        $defaultManagerDef->setArgument($factoryArg, $factoryDef);
                    }

                    if ($repositoryDef) {
                        $defaultManagerDef->setArgument($repositoryArg, $repositoryDef);
                    }

                    $defaultManagerDef->setArgument($formArg, $formDef ?? null);
                }
            }
        }
    }

    /**
     * @param Definition $managerDef
     * @param Definition $formDef
     * @param bool $useMethodCalls
     */
    protected function setFormArguments(
        Definition $formDef,
        bool $useMethodCalls,
        string $classNameArg,
        string $translationDomainArg,
        string $entityClass,
        string $translationDomain
    ): void {
        if (!$useMethodCalls) {
            $formDef->setArgument($classNameArg, $entityClass);
            $formDef->setArgument($translationDomainArg, $translationDomain);
        } else {
            $formDef->addMethodCall('setClassName', [$entityClass]);
            $formDef->addMethodCall('setTranslationDomain', [$translationDomain]);
        }
    }

    /**
     * Not usable
     *
     * @param array $tags
     * @return array
     */
    protected function processFormTags(Definition $formDef): void
    {
        $tags = $formDef->getTags();

        $hasFormTypeTag = false;

        foreach ($tags as $tagName => $tagParams) {
            if ($tagName === static::TAG_FORM_TYPE) {
                $hasFormTypeTag = true;
                break;
            }
        }

        if (!$hasFormTypeTag) {
            $tags[static::TAG_FORM_TYPE] = [0 => []];
        }

        $formDef->setTags($tags);
    }
}
