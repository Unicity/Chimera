<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;

	/**
	 * This class represents a task inverter.
	 *
	 * @access public
	 * @class
	 * @see http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
	 */
	class Inverter extends BT\Task\Decorator {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$status = BT\Task\Handler::process($this->task, $engine, $entityId);
			if ($status == BT\Status::SUCCESS) {
				return BT\Status::FAILED;
			}
			if ($status == BT\Status::FAILED) {
				return BT\Status::SUCCESS;
			}
			return $status;
		}

	}

}