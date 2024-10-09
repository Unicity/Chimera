<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task limiter.
 *
 * @access public
 * @class
 */
class Limiter extends BT\Task\Decorator
{
    /**
     * This variable stores the number of calls that have been made to this task.
     *
     * @access protected
     * @var integer
     */
    protected $calls;

    /**
     * This constructor initializes the class with the specified parameters.
     *
     * @access public
     * @param Common\Mutable\IMap $policy the task's policy
     */
    public function __construct(Common\Mutable\IMap $policy = null)
    {
        parent::__construct($policy);
        if (!$this->policy->hasKey('limit')) {
            $this->policy->putEntry('limit', 1);
        }
        $this->calls = 0;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->calls);
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
        $limit = Core\Convert::toInteger($this->policy->getValue('limit'));
        if ($this->calls < $limit) {
            $status = BT\Task\Handler::process($this->task, $engine, $entityId);
            $this->calls++;

            return $status;
        }

        return BT\Status::FAILED;
    }

    /**
     * This method resets the task.
     *
     * @access public
     * @param BT\Engine $engine the engine
     */
    public function reset(BT\Engine $engine): void
    {
        $this->calls = 0;
    }

}
