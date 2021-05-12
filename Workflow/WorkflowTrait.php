<?php
declare(strict_types=1);

namespace LSB\UtilityBundle\Workflow;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait WorkflowStateTrait
 * @package LSB\UtilityBundle\Traits
 */
trait WorkflowTrait
{
    /**
     * @var WorkflowContextInterface|null
     */
    protected ?WorkflowContextInterface $workflowContext;

    /**
     * State - postęp workflow
     * @ORM\Column(type="json", nullable=true)
     */
    protected array $state = [];

    /**
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * @param $state
     *
     * @return $this
     */
    public function addState($state): self
    {
        if (false === in_array($state, $this->state, true)) {
            $this->state[] = $state;
        }
        return $this;
    }

    /**
     * @param $state
     *
     * @return $this
     */
    public function removeState($state): self
    {
        if (true === in_array($state, $this->state, true)) {
            $index = array_search($state, $this->state);
            array_splice($this->state, $index, 1);
        }
        return $this;
    }

    /**
     * @param array $state
     * @param array $context
     * @return $this
     */
    public function setState(array $state, array $context = []): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return WorkflowContextInterface|null
     */
    public function getWorkflowContext(): ?WorkflowContextInterface
    {
        return $this->workflowContext;
    }

    /**
     * @param WorkflowContextInterface|null $workflowContext
     * @return $this
     */
    public function setWorkflowContext(?WorkflowContextInterface $workflowContext): self
    {
        $this->workflowContext = $workflowContext;
        return $this;
    }
}