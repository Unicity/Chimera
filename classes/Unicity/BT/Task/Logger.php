<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;
use Unicity\Log;

/**
 * This class represents a task logger.
 *
 * @access public
 * @class
 */
class Logger extends BT\Task\Decorator
{
    /**
     * This variable stores the log level.
     *
     * @access protected
     * @var Log\Level
     */
    protected $level;

    /**
     * This constructor initializes the class with the specified parameters.
     *
     * @access public
     * @param Common\Mutable\IMap $policy the task's policy
     */
    public function __construct(Common\Mutable\IMap $policy = null)
    {
        parent::__construct($policy);
        $this->level = Log\Level::informational();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->level);
    }

    /**
     * This method returns whether the logger is enabled.
     *
     * @access public
     * @return boolean whether the logger is enabled
     */
    public function isEnabled(): bool
    {
        if ($this->policy->hasKey('enabled')) {
            return Core\Convert::toBoolean($this->policy->getValue('enabled'));
        }

        return true;
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
        $status = BT\Task\Handler::process($this->task, $engine, $entityId);
        if ($this->isEnabled()) {
            switch ($status) {
                case BT\Status::INACTIVE:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Inactive', [':task' => $this->task]);

                    break;
                case BT\Status::ACTIVE:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Active', [':task' => $this->task]);

                    break;
                case BT\Status::SUCCESS:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Success', [':task' => $this->task]);

                    break;
                case BT\Status::FAILED:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Failed', [':task' => $this->task]);

                    break;
                case BT\Status::ERROR:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Error', [':task' => $this->task]);

                    break;
                case BT\Status::QUIT:
                    $engine->getLogger()->add($this->level, 'Task: :task Status: Quit', [':task' => $this->task]);

                    break;
            }
        }

        return $status;
    }

}
