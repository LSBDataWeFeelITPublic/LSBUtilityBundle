<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
abstract class BaseExtension extends Extension
{
    const CONFIG_PREFIX = 'lsb_bundle_def';
    const DOT = '.';

    const CONFIG_KEY_CONFIG = 'config';
    const CONFIG_KEY_CLASSES = 'classes';
    const CONFIG_KEY_ENTITY = 'entity';
    const CONFIG_KEY_INTERFACE = 'interface';
    const CONFIG_KEY_FACTORY = 'factory';
    const CONFIG_KEY_MANAGER = 'manager';
    const CONFIG_KEY_FORM = 'form';
    const CONFIG_KEY_RESOURCES = 'resources';
    const CONFIG_KEY_TRANSLATION_DOMAIN = 'translation_domain';
    const CONFIG_KEY_TRANSLATION = 'translation';
    const CONFIG_KEY_REPOSITORY = 'repository';
    const CONFIG_KEY_CLASS = 'class';

    const FILE_FACTORIES = 'factories.yml';
    const FILE_MANAGERS = 'managers.yml';
    const FILE_SERVICES = 'services.yml';
    const FILE_VALIDATION = 'validations.yml';
    const FILE_REPOSITORIES = 'repositories.yml';
    const FILE_FORMS = 'forms.yml';

    /**
     * @var string
     */
    protected $dir;

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->dir . '/../Resources/config'));

        $loader->load(static::FILE_FACTORIES);
        $loader->load(static::FILE_MANAGERS);
        $loader->load(static::FILE_SERVICES);

        //Optional
        try {
            $loader->load(static::FILE_REPOSITORIES);
        } catch (\Exception $e) {
        }

        try {
            $loader->load(static::FILE_FORMS);
        } catch (\Exception $e) {
        }

        $configuration = $this->getTreeConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->mapParameters($container, $config, static::CONFIG_PREFIX);
    }

    /**
     * @return ConfigurationInterface
     */
    abstract public function getTreeConfiguration(): ConfigurationInterface;

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @param string $prefix
     * @throws \Exception
     */
    public function mapParameters(ContainerBuilder $container, array $config, string $prefix)
    {

        if (!isset($config[self::CONFIG_KEY_RESOURCES]) || !is_array($config[self::CONFIG_KEY_RESOURCES])) {
//            throw new \Exception('Missing resources array. Check bundle configuration.');
            return;
        }

        $configParameterName = $prefix.static::DOT.static::CONFIG_KEY_CONFIG;
        $resourcesParameterName = $prefix.static::DOT.static::CONFIG_KEY_CONFIG.static::DOT.static::CONFIG_KEY_RESOURCES;
        $translationDomainParameterName = $prefix.static::DOT.static::CONFIG_KEY_CONFIG.static::DOT.static::CONFIG_KEY_TRANSLATION_DOMAIN;

        $container->setParameter($configParameterName, $config ?? []);
        $container->setParameter($resourcesParameterName, $config[self::CONFIG_KEY_RESOURCES] ?? []);
        $container->setParameter($translationDomainParameterName, $config[self::CONFIG_KEY_TRANSLATION_DOMAIN] ?? null);

        $classesList = [
            static::CONFIG_KEY_ENTITY,
            static::CONFIG_KEY_FACTORY,
            static::CONFIG_KEY_INTERFACE,
            static::CONFIG_KEY_REPOSITORY,
            static::CONFIG_KEY_MANAGER,
            static::CONFIG_KEY_FORM
        ];

        $translationList = [
            static::CONFIG_KEY_ENTITY,
            static::CONFIG_KEY_FACTORY,
            static::CONFIG_KEY_INTERFACE,
            static::CONFIG_KEY_REPOSITORY
        ];

        /**
         * @var array $data
         */
        foreach ($config[self::CONFIG_KEY_RESOURCES] as $class => $data) {
            //Classes
            foreach($classesList as $classListData) {
                $this->setConfigParameter($container, $data, $prefix, $class, $classListData);
            }
            //Translations
            foreach($translationList as $classListData) {
                $this->setConfigParameter($container, $data, $prefix, $class, $classListData, true);
            }
        }
    }

    /**
     * @param string $prefix
     * @param string $class
     * @param string $key
     * @param bool $isTranslation
     * @return string
     */
    public function generateConfigParameterName(string $prefix, string $class, string $key, bool $isTranslation = false): string
    {
        return $prefix.'.'.static::CONFIG_KEY_CONFIG.'.'.static::CONFIG_KEY_RESOURCES.'.'.$class.'.'.($isTranslation ? static::CONFIG_KEY_TRANSLATION : static::CONFIG_KEY_CLASS).'.'.$key;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $data
     * @param string $prefix
     * @param string $class
     * @param string $key
     * @param bool $isTranslation
     */
    public function setConfigParameter(ContainerBuilder $container, array $data, string $prefix, string $class, string $key, bool $isTranslation = false): void
    {
        $container->setParameter(
            $this->generateConfigParameterName($prefix, $class, $key, $isTranslation),
            $data[($isTranslation ? static::CONFIG_KEY_TRANSLATION : static::CONFIG_KEY_CLASSES)][$key] ?? null
        );
    }
}
