<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;

	/**
	 * This class represents a task scheduler.
	 *
	 * @access public
	 * @class
	 */
	class Scheduler extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$engine->getEntity($entityId)->setTaskId($this->policy->getValue('task'));
			return BT\Status::ACTIVE;
		}

	}

}