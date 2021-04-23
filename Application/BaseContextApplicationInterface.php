<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Application;

use LSB\UtilityBundle\Application\ApplicationContextInterface;

/**
 * Interface BaseContextApplicationInterface
 * @package LSB\UtilityBundle\Application
 */
interface BaseContextApplicationInterface extends ApplicationContextInterface
{
    const TAG = 'app.context.base';
    const CODE = 'base';
}