<?php

declare(strict_types=1);

namespace Unicity\BT\Task;

use Unicity\BT;
use Unicity\Core;

class HasComponentAtPath extends BT\Task\Guard
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
        $blackboard = $engine->getBlackboard($this->policy->getValue('blackboard'));
        $name = Core\Convert::toString($this->policy->getValue('component'));
        $key = $entityId . '.' . $name;

        if ($blackboard->hasKey($key)) {
            $path = $blackboard->getValue($key);
            if (is_string($path) && ($path != '')) {
                return BT\Status::SUCCESS;
            }
        }

        return BT\Status::FAILED;
    }

}
