<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;

/**
 * This class represents a task timer.
 *
 * @access public
 * @class
 * @see https://en.wikipedia.org/wiki/Semaphore_%28programming%29
 */
class Semaphore extends BT\Task\Decorator
{
    /**
     * This constructor initializes the class with the specified parameters.
     *
     * @access public
     * @param Common\Mutable\IMap $policy the task's policy
     */
    public function __construct(Common\Mutable\IMap $policy = null)
    {
        parent::__construct($policy);
        if (!$this->policy->hasKey('id')) {
            $this->policy->putEntry('id', __CLASS__);
        }
    }

    /**
     * This method processes an entity.
     *
     * @access public
     * @param BT\Engine $engine the engine running
     * @param string $entityId the entity id being processed
     * @return integer the status
     */
    public function process(BT\Engine $engine, string $entityId): int
    {
        $blackboard = $engine->getBlackboard($this->policy->getValue('blackboard'));
        $id = $this->policy->getValue('id');

        if ($blackboard->hasKey($id)) {
            $hashCode = $blackboard->getValue($id);
            if ($hashCode == $this->task->__hashCode()) {
                $status = BT\Task\Handler::process($this->task, $engine, $entityId);
                if ($status != BT\Status::ACTIVE) {
                    $blackboard->removeKey($id);
                }

                return $status;
            }

            return BT\Status::ACTIVE;
        } else {
            $status = BT\Task\Handler::process($this->task, $engine, $entityId);
            if ($status == BT\Status::ACTIVE) {
                $blackboard->putEntry($id, $this->task->__hashCode());
            }

            return $status;
        }
    }

    /**
     * This method resets the task.
     *
     * @access public
     * @param BT\Engine $engine the engine
     */
    public function reset(BT\Engine $engine): void
    {
        $blackboard = $engine->getBlackboard($this->policy->getValue('blackboard'));
        $blackboard->removeKey($this->policy->getValue('id'));
    }

}
