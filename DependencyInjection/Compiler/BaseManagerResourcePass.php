<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection\Compiler;

use LSB\UtilityBundle\Application\BaseContextApplicationInterface;
use LSB\UtilityBundle\DependencyInjection\Model\ResourceClassesConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LSB\UtilityBundle\DependencyInjection\BaseExtension as BE;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class BaseManagerResourcePass
 * @package LSB\UtilityBundle\DependencyInjection\Compiler
 */
abstract class BaseManagerResourcePass implements CompilerPassInterface
{
    /**
     * @var array
     */
    protected $processedServices = [];

    const CONFIG_KEY_CONFIG = 'config';
    const CONFIG_KEY_CLASSES = 'classes';
    const CONFIG_KEY_FACTORY = 'factory';
    const CONFIG_KEY_MANAGER = 'manager';
    const CONFIG_KEY_ENTITY = 'entity';
    const CONFIG_KEY_REPOSITORY = 'repository';
    const CONFIG_KEY_RESOURCES = 'resources';

    const TAG_FORM_TYPE = 'form.type';

    const PARAMETER_APP_CONTEXTS = 'app.contexts';

    const ARGUMENT_FACTORY= '$factory';
    const ARGUMENT_REPOSITORY = '$repository';
    const ARGUMENT_FORM = '$form';
    const ARGUMENT_TRANSLATION_DOMAIN = '$translationDomain';
    const ARGUMENT_CLASSNAME = '$className';
    const ARGUMENT_ENTITY_CLASS = '$entityClass';
    const ARGUMENT_APP_CODE = '$appCode';

    /**
     * @param ContainerBuilder $container
     * @param string $prefix
     * @param bool $useMethodCalls
     * @throws \ReflectionException
     */
    protected function processResources(
        ContainerBuilder $container,
        string $prefix,

        bool $useMethodCalls = true
    ): void {

        $appContextCodes = $this->getAppContexts($container);

        $resourcesParameterName = $prefix . '.' . BE::CONFIG_KEY_CONFIG . '.' . BE::CONFIG_KEY_RESOURCES;
        $translationDomainParameterName = $prefix . '.' . BE::CONFIG_KEY_CONFIG . '.' . BE::CONFIG_KEY_TRANSLATION_DOMAIN;

        if (!$container->hasParameter($resourcesParameterName)) {
//            throw new \InvalidArgumentException("Missing $prefix resource parameter: $resourcesParameterName");
            return;
        }
        $configParameterName = $prefix.BE::DOT.BE::CONFIG_KEY_CONFIG;
        $config = $container->getParameter($configParameterName);

        $resources = $container->getParameter($resourcesParameterName);
        $translationDomain = $container->getParameter($translationDomainParameterName);

        /**
         * @var array $data
         */
        foreach ($resources as $resource => $data) {
            //Base
            $this->configureResourceContext($container, null, $resource, $data, $useMethodCalls, $config, $translationDomain);


            foreach ($appContextCodes as $appContextCode) {
                $this->configureResourceContext($container, $appContextCode, $resource, $data, $useMethodCalls, $config, $translationDomain);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string|null $appContextCode
     * @param string $resource
     * @param array $data
     * @param bool $useMethodCalls
     * @param array $config
     * @param string $translationDomain
     * @throws \ReflectionException
     */
    protected function configureResourceContext(
        ContainerBuilder $container,
        ?string $appContextCode,
        string $resource,
        array $data,
        bool $useMethodCalls,
        array &$config,
        string $translationDomain
    ): void {
        //get data
        $resourceConfiguration = $this->getClassesFromResourceData($container, $appContextCode, $data);

        $this->setManagerArguments(
            $appContextCode,
            $resourceConfiguration->getManagerDefinition(),
            $resourceConfiguration->getFactoryDefinition(),
            $resourceConfiguration->getRepositoryDefinition(),
            $resourceConfiguration->getFormDefinition(),
            $config,
            $data
        );

        //Manager parents
        $this->setManagerParentsArguments(
            $container,
            $resourceConfiguration->getManagerClass(),
            $resourceConfiguration->getFactoryDefinition(),
            $resourceConfiguration->getRepositoryDefinition(),
            $resourceConfiguration->getFormDefinition(),
            $config,
            $data
        );

        $this->setFactoryArguments($appContextCode, $resourceConfiguration->getFactoryDefinition(), $resourceConfiguration->getEntityClass());

        $this->setRepositoryArguments($appContextCode, $resourceConfiguration->getRepositoryDefinition(), $resourceConfiguration->getEntityClass());

        if ($resourceConfiguration->getFormDefinition()) {
            $this->setFormArguments($appContextCode, $resourceConfiguration->getFormDefinition(), $useMethodCalls, $resourceConfiguration->getEntityClass(), $translationDomain);
        }

        if ($resourceConfiguration->getFormClass()) {
            $this->setFormParentsArguments($container, $resourceConfiguration->getFormClass(), $useMethodCalls, $resourceConfiguration->getEntityClass(), $translationDomain);
        }

        if ($resourceConfiguration->getTranslationFormDefinition()) {
            $this->setFormArguments($appContextCode, $resourceConfiguration->getTranslationFormDefinition(), $useMethodCalls, $resourceConfiguration->getEntityClass(), $translationDomain);
        }

        if ($resourceConfiguration->getTranslationFormClass()) {
            $this->setFormParentsArguments($container, $resourceConfiguration->getTranslationFormClass(), $useMethodCalls, $resourceConfiguration->getEntityClass(), $translationDomain);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string|null $appContextCode
     * @param array $data
     * @param bool $fetchServiceDefinitions
     * @return ResourceClassesConfiguration
     */
    protected function getClassesFromResourceData(ContainerBuilder $container, ?string $appContextCode, array &$data, bool $fetchServiceDefinitions = true): ResourceClassesConfiguration
    {

        $resourceClassesConfiguration = new ResourceClassesConfiguration();

        if (isset($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY])) {
            $resourceClassesConfiguration->setEntityClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_ENTITY]);
        }

        if (isset($data[BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_ENTITY])) {
            $resourceClassesConfiguration->setTranslationEntityClass($data[BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_ENTITY]);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_MANAGER])) {
            $resourceClassesConfiguration->setManagerClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_MANAGER]);
        } else {
            $resourceClassesConfiguration->setManagerClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_MANAGER] ?? null);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FACTORY])) {
            $resourceClassesConfiguration->setFactoryClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FACTORY]);
        } else {
            $resourceClassesConfiguration->setFactoryClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FACTORY] ?? null);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM])) {
            $resourceClassesConfiguration->setFormClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM]);
        } else {
            $resourceClassesConfiguration->setFormClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_FORM] ?? null);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_REPOSITORY])) {
            $resourceClassesConfiguration->setRepositoryClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_REPOSITORY]);
        } else {
            $resourceClassesConfiguration->setRepositoryClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_REPOSITORY] ?? null);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_FORM])) {
            $resourceClassesConfiguration->setTranslationFormClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_FORM]);
        } else {
            $resourceClassesConfiguration->setTranslationFormClass($data[BE::CONFIG_KEY_TRANSLATION][BE::CONFIG_KEY_FORM] ?? null);
        }

        if ($appContextCode && isset($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT])) {
            $resourceClassesConfiguration->setVoterSubjectClass($data[BE::CONFIG_KEY_CONTEXT][$appContextCode][BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT]);
        } else {
            $resourceClassesConfiguration->setVoterSubjectClass($data[BE::CONFIG_KEY_CLASSES][BE::CONFIG_KEY_VOTER_SUBJECT] ?? null);
        }

        //Fetch services def
        if ($fetchServiceDefinitions) {
            if ($resourceClassesConfiguration->getManagerClass() && $container->hasDefinition($resourceClassesConfiguration->getManagerClass())) {
                $resourceClassesConfiguration->setManagerDefinition($container->getDefinition($resourceClassesConfiguration->getManagerClass()));
            }

            if ($resourceClassesConfiguration->getFactoryClass() && $container->hasDefinition($resourceClassesConfiguration->getFactoryClass())) {
                $resourceClassesConfiguration->setFactoryDefinition($container->getDefinition($resourceClassesConfiguration->getFactoryClass()));
            }

            if ($resourceClassesConfiguration->getFormClass() && $container->hasDefinition($resourceClassesConfiguration->getFormClass())) {
                $resourceClassesConfiguration->setFormDefinition($container->getDefinition($resourceClassesConfiguration->getFormClass()));
            }


            if ($resourceClassesConfiguration->getTranslationFormClass() && $container->hasDefinition($resourceClassesConfiguration->getTranslationFormClass())) {
                $resourceClassesConfiguration->setTranslationFormDefinition($container->getDefinition($resourceClassesConfiguration->getTranslationFormClass()));
            }

            if ($resourceClassesConfiguration->getRepositoryClass() && $container->hasDefinition($resourceClassesConfiguration->getRepositoryClass())) {
                $resourceClassesConfiguration->setRepositoryDefinition($container->getDefinition($resourceClassesConfiguration->getRepositoryClass()));
            }
        }

        return $resourceClassesConfiguration;
    }

    /**
     * @param string|null $appCode
     * @param Definition|null $managerDef
     * @param Definition|null $factoryDef
     * @param Definition|null $repositoryDef
     * @param Definition|null $formDef
     * @param array $bundleConfiguration
     * @param array $resourceConfiguration
     */
    protected function setManagerArguments(
        ?string $appCode,
        ?Definition $managerDef,
        ?Definition $factoryDef,
        ?Definition $repositoryDef,
        ?Definition $formDef,
        array $bundleConfiguration = [],
        array $resourceConfiguration = []
    ): void {

        if ($this->isServiceProcessed($managerDef->getClass())) {
            return;
        }

        if ($factoryDef) {
            $managerDef->setArgument(static::ARGUMENT_FACTORY, $factoryDef);
        }

        if ($repositoryDef) {
            $managerDef->setArgument(static::ARGUMENT_REPOSITORY, $repositoryDef);
        }

        $managerDef->setArgument(static::ARGUMENT_FORM, $formDef ?? null);

        $managerDef->addMethodCall('setBundleConfiguration', [$bundleConfiguration]);
        $managerDef->addMethodCall('setResourceConfiguration', [$resourceConfiguration]);
        $managerDef->addMethodCall('setAppCode', [$appCode]);

        $this->markServiceAsProcessed($managerDef->getClass());
    }

    /**
     * @param Container $container
     * @param string $managerClass
     * @param Definition|null $factoryDef
     * @param Definition|null $repositoryDef
     * @param Definition|null $formDef
     * @param array $bundleConfiguration
     * @param array $resourceConfiguration
     * @throws \ReflectionException
     */
    public function setManagerParentsArguments(
        Container $container,
        string $managerClass,
        ?Definition $factoryDef,
        ?Definition $repositoryDef,
        ?Definition $formDef,
        array $bundleConfiguration = [],
        array $resourceConfiguration = []
    ): void {
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
                $this->setManagerArguments(null, $defaultManagerDef, $factoryDef, $repositoryDef, $formDef, $bundleConfiguration, $resourceConfiguration);
            }
        }
    }

    /**
     * @param string|null $appCode
     * @param Definition $formDef
     * @param bool $useMethodCalls
     * @param string $entityClass
     * @param string $translationDomain
     */
    protected function setFormArguments(
        ?string $appCode,
        Definition $formDef,
        bool $useMethodCalls,
        string $entityClass,
        string $translationDomain
    ): void {
        if ($this->isServiceProcessed($formDef->getClass())) {
            return;
        }

        $formDef->addMethodCall('setAppCode', [$appCode]);

        if (!$useMethodCalls) {
            $formDef->setArgument(static::ARGUMENT_CLASSNAME, $entityClass);
            $formDef->setArgument(static::ARGUMENT_TRANSLATION_DOMAIN, $translationDomain);
        } else {
            $formDef->addMethodCall('setClassName', [$entityClass]);
            $formDef->addMethodCall('setTranslationDomain', [$translationDomain]);
        }

        $this->markServiceAsProcessed($formDef->getClass());
    }

    /**
     * @param ContainerBuilder $container
     * @param string $formClass
     * @param bool $useMethodCalls
     * @param string $entityClass
     * @param string $translationDomain
     * @throws \ReflectionException
     */
    protected function setFormParentsArguments(
        ContainerBuilder $container,
        string  $formClass,
        bool $useMethodCalls,
        string $entityClass,
        string $translationDomain
    ): void {
        $parentsClasses = class_parents($formClass);

        /**
         * @var string $parentsClass
         */
        foreach ($parentsClasses as $parentsClass) {
            $parentsClassReflection = new \ReflectionClass($parentsClass);
            if ($parentsClassReflection->isAbstract()) {
                continue;
            }

            $defaultTranslationFormDef = $container->getDefinition($parentsClass);
            $this->setFormArguments(null, $defaultTranslationFormDef, $useMethodCalls, $entityClass, $translationDomain);
        }
    }

    /**
     * @param string|null $appCode
     * @param Definition|null $factoryDef
     * @param string $entityClass
     */
    protected function setFactoryArguments(
        ?string $appCode,
        ?Definition $factoryDef,
        string $entityClass
    ): void {
        //Factory
        if ($factoryDef) {
            if ($this->isServiceProcessed($factoryDef->getClass())) {
                return;
            }

            $factoryDef->setArgument(static::ARGUMENT_CLASSNAME, $entityClass);
            $factoryDef->addMethodCall('setAppCode', [$appCode]);

            $this->markServiceAsProcessed($factoryDef->getClass());
        }
    }

    /**
     * @param string|null $appCode
     * @param Definition $repositoryDef
     * @param string $entityClass
     */
    protected function setRepositoryArguments(
        ?string $appCode,
        Definition $repositoryDef,
        string $entityClass
    ): void {
        //Factory
        if ($repositoryDef) {
            if ($this->isServiceProcessed($repositoryDef->getClass())) {
                return;
            }

            //Disabled
            //$repositoryDef->setArgument(static::ARGUMENT_ENTITY_CLASS, $entityClass);

            $repositoryDef->addMethodCall('setAppCode', [$appCode]);
            $this->markServiceAsProcessed($repositoryDef->getClass());
        }
    }

    /**
     * Not usable
     *
     * @param Definition $formDef
     * @return void
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

    /**
     * @param ContainerBuilder $container
     * @return array
     * @throws \Exception
     */
    protected function getAppContexts(ContainerBuilder $container): array
    {
        if (!$container->hasParameter(self::PARAMETER_APP_CONTEXTS))
        {
            throw new \Exception('You must define app.contexts parameter with app codes array.');
        }

        $appContexts = $container->getParameter(self::PARAMETER_APP_CONTEXTS);

        if (count($appContexts) === 0) {
            throw new \Exception('At least one application context is required.');
        }

        return $appContexts;
    }

    /**
     * @param string $fqcn
     */
    public function markServiceAsProcessed(string $fqcn): void
    {
        $this->processedServices[$fqcn] = $fqcn;
    }

    /**
     * @param string $fqcn
     * @return bool
     */
    public function isServiceProcessed(string $fqcn): bool
    {
        if (array_key_exists($fqcn, $this->processedServices)) {
            return true;
        }

        return false;
    }


}