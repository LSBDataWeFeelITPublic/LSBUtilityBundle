<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Form;

use LSB\UtilityBundle\Form\DataTransformer\EntityLazyTransformer;
use LSB\UtilityBundle\Manager\ObjectManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class EntityLazyType
 * @package LSB\UtilityBundle\Form
 */
class EntityLazyType extends AbstractType
{
    /**
     * @var ObjectManagerInterface
     */
    private $om;

    /**
     * EntitySimpleAutocompleterType constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->om = $objectManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EntityLazyTransformer($this->om, $options['class'], $options['value_field'], $options['validate_uuid'], $options['required']);
        $builder->addViewTransformer($transformer);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        //Not supported
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder' => null,
            'route' => null,
            'default_data_array' => [],
            'choice_label' => 'name',
            'choice_label_arguments' => [],
            'useToString' => false,
            'minimumInputLength' => 1,
            'route_params' => [],
            'class' => null,
            'value_field' => 'uuid',
            'validate_uuid' => true,
            'choice_value' => 'uuid',
            'required' => true
        ]);


        $resolver->setRequired(['class']);
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return 'entity_lazy_type';
    }
}
