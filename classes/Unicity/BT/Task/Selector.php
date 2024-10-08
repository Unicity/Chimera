<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task selector.
 *
 * @access public
 * @class
 * @see http://aigamedev.com/open/article/selector/
 */
class Selector extends BT\Task\Branch
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
        // frequency: once, each
        // order: shuffle, weight, fixed
        if (!$this->policy->hasKey('shuffle')) {
            $this->policy->putEntry('shuffle', false);
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
        $shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
        if ($shuffle) {
            $this->tasks->shuffle();
        }
        $inactives = 0;
        foreach ($this->tasks as $task) {
            $status = BT\Task\Handler::process($task, $engine, $entityId);
            if (in_array($status, [BT\Status::ACTIVE, BT\Status::SUCCESS, BT\Status::ERROR, BT\Status::QUIT])) {
                return $status;
            }
            if ($status == BT\Status::INACTIVE) {
                $inactives++;
            }
        }

        return ($inactives < $this->tasks->count()) ? BT\Status::FAILED : BT\Status::INACTIVE;
    }

}
