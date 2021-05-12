<?php

namespace LSB\UtilityBundle\Workflow;

use LSB\UtilityBundle\Application\AppCodeTrait;
use LSB\UtilityBundle\Application\ApplicationContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;


/**
 * Class BaseWorkflowSubscriber
 * @package LSB\UtilityBundle\Workflow
 */
abstract class BaseWorkflowSubscriber implements ApplicationContextInterface, EventSubscriberInterface
{
    use AppCodeTrait;

    /**
     * @param Event $event
     * @return bool
     */
    public function isAppContextMatched(Event $event): bool
    {

        $subject = $event->getSubject();

        if (!$subject || !$subject instanceof WorkflowableInterface || !$subject->getWorkflowContext()) {
            return false;
        }

        if ($subject->getWorkflowContext()->getAppCode() === $this->getAppCode(true)) {
            return true;
        }

        return false;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function isSubjectWorkflowable(Event $event): bool
    {
        if (!$event->getSubject() instanceof WorkflowableInterface) {
            return false;
        }

        return true;
    }
}