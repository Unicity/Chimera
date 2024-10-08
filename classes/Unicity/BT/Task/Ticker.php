<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task ticker.
 *
 * @access public
 * @class
 * @see http://cc.byexamples.com/2007/03/14/create-a-timer/
 * @see http://stackoverflow.com/questions/12783737/how-to-use-setinterval-in-php
 */
class Ticker extends BT\Task\Decorator
{
    /**
     * This variable stores the time of when the ticker will tick next.
     *
     * @access protected
     * @var float
     */
    protected $next_time;

    /**
     * This constructor initializes the class with the specified parameters.
     *
     * @access public
     * @param Common\Mutable\IMap $policy the task's policy
     */
    public function __construct(Common\Mutable\IMap $policy = null)
    {
        parent::__construct($policy);
        if (!$this->policy->hasKey('interval')) {
            $this->policy->putEntry('interval', 1000); // 1 millisecond = 1/1000 of a second
        }
        $this->next_time = microtime(true) + (Core\Convert::toInteger($this->policy->getValue('interval')) / 1000);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->next_time);
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
        $interval = Core\Convert::toInteger($this->policy->getValue('interval')) / 1000; // milliseconds => seconds

        if (microtime(true) >= $this->next_time) {
            $status = BT\Task\Handler::process($this->task, $engine, $entityId);
            $this->next_time += $interval;

            return $status;
        }

        return BT\Status::ACTIVE;
    }

    /**
     * This method resets the task.
     *
     * @access public
     * @param BT\Engine $engine the engine
     */
    public function reset(BT\Engine $engine): void
    {
        $this->next_time = microtime(true) + (Core\Convert::toInteger($this->policy->getValue('interval')) / 1000);
    }

}
