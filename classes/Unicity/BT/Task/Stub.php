<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Common;
use Unicity\Core;

/**
 * This class represents a task stub.
 *
 * @access public
 * @class
 */
class Stub extends BT\Task\Leaf
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
        if ($this->policy->hasKey('status')) {
            $status = $this->policy->getValue('status');
            $status = (is_string($status))
                ? BT\Status::valueOf(strtoupper($status))
                : Core\Convert::toInteger($status);
            $this->policy->putEntry('status', $status);
        } else {
            $this->policy->putEntry('status', BT\Status::SUCCESS);
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
        return $this->policy->getValue('status');
    }

}
