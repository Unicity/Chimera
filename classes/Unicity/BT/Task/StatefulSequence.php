<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;

/**
 * This class represents a task stateful sequence.
 *
 * @access public
 * @class
 */
class StatefulSequence extends BT\Task\Sequence
{
    /**
     * This variable stores the last state of the sequence.
     *
     * @access public
     * @var integer
     */
    protected $state;

    /**
     * This constructor initializes the class with the specified parameters.
     *
     * @access public
     * @param Common\Mutable\IMap $policy the task's policy
     */
    public function __construct(Common\Mutable\IMap $policy = null)
    {
        parent::__construct($policy);
        $this->state = 0;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->state);
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
        $inactives = 0;
        while ($this->state < $this->tasks->count()) {
            $status = BT\Task\Handler::process($this->tasks->getValue($this->state), $engine, $entityId);
            if ($status == BT\Status::INACTIVE) {
                $inactives++;
            } elseif ($status == BT\Status::ACTIVE) {
                return $status;
            } elseif ($status != BT\Status::SUCCESS) {
                $this->state = 0;

                return $status;
            }
            $this->state++;
        }
        $this->state = 0;

        return ($inactives < $this->tasks->count()) ? BT\Status::SUCCESS : BT\Status::INACTIVE;
    }

    /**
     * This method resets the task.
     *
     * @access public
     * @param BT\Engine $engine the engine
     */
    public function reset(BT\Engine $engine): void
    {
        $this->state = 0;
    }

}
