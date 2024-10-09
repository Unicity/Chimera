<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;

/**
 * This class represents a task condition.
 *
 * @access public
 * @class
 * @see https://docs.unrealengine.com/latest/INT/Engine/AI/BehaviorTrees/HowUE4BehaviorTreesDiffer/index.html
 * @see https://sourcemaking.com/refactoring/replace-nested-conditional-with-guard-clauses
 * @see http://www.tutisani.com/software-architecture/nested-if-vs-guard-condition.html
 */
final class Condition extends BT\Task\Composite
{
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
        $status = BT\Task\Handler::process($this->tasks->getValue(0), $engine, $entityId);
        if ($status == BT\Status::SUCCESS) {
            return BT\Task\Handler::process($this->tasks->getValue(1), $engine, $entityId);
        }

        return $status;
    }

}
