<?php

declare(strict_types=1);

namespace LSB\UtilityBundle\Translatable;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait TranslatablePropertiesTrait
 * @package LSB\UtilityBundle\Translatable
 */
trait TranslatablePropertiesTrait
{
    /**
     * @var TranslationInterface[]|Collection
     */
    protected $translations;

    /**
     * @see mergeNewTranslations
     * @var TranslationInterface[]|Collection
     */
    protected $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     * @var string|null
     */
    protected $currentLocale;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';
}
