<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;

/**
 * This class represents a task random selector.
 *
 * @access public
 * @class
 */
class RandomPicker extends BT\Task\Picker
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
        if (!$this->policy->hasKey('callable')) {
            $this->policy->putEntry('callable', 'rand'); // ['rand', 'mt_rand']
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
        $callable = explode(',', $this->policy->getValue('callable'));
        $count = $this->tasks->count();
        if ($count > 0) {
            $index = call_user_func($callable, [0, $count]);
            $task = $this->tasks->getValue($index);

            return BT\Task\Handler::process($task, $engine, $entityId);
        }

        return BT\Status::ERROR;
    }

}
