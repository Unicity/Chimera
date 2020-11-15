<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task counter.
	 *
	 * @access public
	 * @class
	 */
	class Counter extends BT\Task\Decorator {

		/**
		 * This variable stores the counter.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $counter;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('max_count')) {
				$this->policy->putEntry('max_count', 10);
			}
			$this->counter = 0;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->counter);
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
			$max_count = Core\Convert::toInteger($this->policy->getValue('max_count'));
			if ($this->counter < $max_count) {
				$this->counter++;
				return BT\Status::ACTIVE;
			}
			$this->counter = 0;
			return BT\Task\Handler::process($this->task, $engine, $entityId);
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine
		 */
		public function reset(BT\Engine $engine) : void {
			$this->counter = 0;
		}

	}

}