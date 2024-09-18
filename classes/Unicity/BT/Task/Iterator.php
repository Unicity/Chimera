<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task iterator.
 *
 * @access public
 * @class
 * @see http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
 */
class Iterator extends BT\Task\Decorator
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
        if (!$this->policy->hasKey('reverse')) { // direction
            $this->policy->putEntry('reverse', false);
        }
        if (!$this->policy->hasKey('steps')) {
            $this->policy->putEntry('steps', 1);
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
        $steps = Core\Convert::toInteger($this->policy->getValue('steps'));
        if ($this->policy->getValue('reverse')) { // direction
            for ($i = $steps - 1; $i >= 0; $i--) {
                $status = BT\Task\Handler::process($this->task, $engine, $entityId);
                if (!in_array($status, [BT\Status::SUCCESS, BT\Status::FAILED, BT\Status::ERROR, BT\Status::QUIT])) {
                    return $status;
                }
            }
        } else {
            for ($i = 0; $i < $steps; $i++) {
                $status = BT\Task\Handler::process($this->task, $engine, $entityId);
                if (!in_array($status, [BT\Status::SUCCESS, BT\Status::FAILED, BT\Status::ERROR, BT\Status::QUIT])) {
                    return $status;
                }
            }
        }

        return BT\Status::SUCCESS;
    }

}
