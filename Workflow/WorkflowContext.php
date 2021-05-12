<?php

namespace LSB\UtilityBundle\Workflow;

use LSB\UtilityBundle\Application\AppCodeTrait;

/**
 * Class WorkflowContext
 * @package LSB\UtilityBundle\Workflow
 */
class WorkflowContext implements WorkflowContextInterface
{
    use AppCodeTrait;

    /**
     * WorkflowContext constructor.
     * @param string $appCode
     */
    public function __construct(string $appCode)
    {
        $this->appCode = $appCode;
    }
}