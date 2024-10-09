<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task parallel.
 *
 * @access public
 * @class
 * @see http://aigamedev.com/open/article/parallel/
 */
class Parallel extends BT\Task\Branch
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
        if (!$this->policy->hasKey('successes')) {
            $this->policy->putEntry('successes', 1);
        }
        if (!$this->policy->hasKey('failures')) {
            $this->policy->putEntry('failures', 1);
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
        $count = $this->tasks->count();
        if ($count > 0) {
            $shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
            if ($shuffle) {
                $this->tasks->shuffle();
            }
            $inactivesCt = 0;
            $successesCt = 0;
            $successesMax = min(Core\Convert::toInteger($this->policy->getValue('successes')), $count);
            $failuresCt = 0;
            $failuresMax = min(Core\Convert::toInteger($this->policy->getValue('failures')), $count);
            foreach ($this->tasks as $task) {
                $status = BT\Task\Handler::process($task, $engine, $entityId);
                switch ($status) {
                    case BT\Status::INACTIVE:
                        $inactivesCt++;

                        break;
                    case BT\Status::ACTIVE:
                        break;
                    case BT\Status::SUCCESS:
                        $successesCt++;
                        if ($successesCt >= $successesMax) {
                            return $status;
                        }

                        break;
                    case BT\Status::FAILED:
                        $failuresCt++;
                        if ($failuresCt >= $failuresMax) {
                            return $status;
                        }

                        break;
                    case BT\Status::ERROR:
                    case BT\Status::QUIT:
                        return $status;
                }
            }
            if ($inactivesCt != $count) {
                return BT\Status::ACTIVE;
            }
        }

        return BT\Status::INACTIVE;
    }

}
