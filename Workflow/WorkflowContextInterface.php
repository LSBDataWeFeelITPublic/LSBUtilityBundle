<?php

namespace LSB\UtilityBundle\Workflow;

use LSB\UtilityBundle\Application\ApplicationContextInterface;

/**
 * Interface WorkflowContextInterface
 * @package LSB\UtilityBundle\Workflow
 */
interface WorkflowContextInterface extends ApplicationContextInterface
{
    const CONTEXT_OBJECT = 'contextObject';
}