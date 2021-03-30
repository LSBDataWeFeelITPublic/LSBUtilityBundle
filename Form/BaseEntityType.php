<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseEntityType
 * @package LSB\UtilityBundle\Form
 */
abstract class BaseEntityType extends AbstractType
{
    const DEFAULT_TRANSLATION_DOMAIN = 'messages';

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @param string $translationDomain
     */
    public function setTranslationDomain(string $translationDomain): void
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        if (!$this->className) {
            throw new \Exception('FQCN is required.');
        }

        $resolver->setDefaults(
            [
                'translation_domain' => $this->translationDomain,
                'data_class' => $this->className,
                'csrf_protection' => false,
                'error_bubbling' => true
            ]
        );
    }
}
