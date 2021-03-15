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
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $translationDomain;

    /**
     * BaseEntityType constructor.
     * @param string $className
     * @param string $translationDomain
     */
    public function __construct(string $className, string $translationDomain)
    {
        $this->className = $className;
        $this->translationDomain = $translationDomain;
    }

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
