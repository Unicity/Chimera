<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;

/**
 * This class represents a task resetter.
 *
 * @access public
 * @class
 * @see http://magicscrollsofcode.blogspot.com/2010/12/behavior-trees-by-example-ai-in-android.html
 */
class Resetter extends BT\Task\Decorator
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
        $status = BT\Task\Handler::process($this->task, $engine, $entityId);
        if ($status == BT\Status::SUCCESS) {
            $this->task->reset($engine);
        }

        return $status;
    }

}
