<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;

	/**
	 * This class represents a task stateful selector.
	 *
	 * @access public
	 * @class
	 */
	class StatefulSelector extends BT\Task\Selector {

		/**
		 * This variable stores the last state of the selector.
		 *
		 * @access public
		 * @var integer
		 */
		protected $state;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			$this->state = 0;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->state);
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$inactives = 0;
			while ($this->state < $this->tasks->count()) {
				$status = BT\Task\Handler::process($this->tasks->getValue($this->state), $engine, $entityId);
				if (in_array($status, array(BT\Status::SUCCESS, BT\Status::ERROR, BT\Status::QUIT))) {
					$this->state = 0;
					return $status;
				}
				else if ($status == BT\Status::ACTIVE) {
					return $status;
				}
				else if ($status == BT\Status::INACTIVE) {
					$inactives++;
				}
				$this->state++;
			}
			$this->state = 0;
			return ($inactives < $this->tasks->count()) ? BT\Status::FAILED : BT\Status::INACTIVE;
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine
		 */
		public function reset(BT\Engine $engine) : void {
			$this->state = 0;
		}

	}

}