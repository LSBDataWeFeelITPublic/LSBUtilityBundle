<?php

namespace LSB\UtilityBundle\Workflow;

/**
 * Interface WorkflowableInterface
 * @package LSB\UtilityBundle\Workflow
 */
interface WorkflowableInterface
{
    /**
     * @return WorkflowContextInterface|null
     */
    public function getWorkflowContext(): ?WorkflowContextInterface;

    /**
     * @param WorkflowContextInterface|null $workflowContext
     * @return $this
     */
    public function setWorkflowContext(?WorkflowContextInterface $workflowContext): self;
}