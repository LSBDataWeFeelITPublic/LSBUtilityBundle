<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Translatable;

//W użyciu własny trait
//use Knp\DoctrineBehaviors\Model\Translatable\TranslatablePropertiesTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethodsTrait;

/**
 * Trait TranslatableTrait
 * @package LSB\UtilityBundle\Translatable
 */
trait TranslatableTrait
{
    use TranslatablePropertiesTrait;
    use TranslatableMethodsTrait;
}
