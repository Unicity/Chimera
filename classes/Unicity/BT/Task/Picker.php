<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task picker.
	 *
	 * @access public
	 * @class
	 */
	class Picker extends BT\Task\Branch {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('index')) {
				$this->policy->putEntry('index', 0);
			}
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
			$shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
			if ($shuffle) {
				$this->tasks->shuffle();
			}
			$index = Core\Convert::toInteger($this->policy->getValue('index'));
			if ($this->tasks->hasIndex($index)) {
				return BT\Task\Handler::process($this->tasks->getValue($index), $engine, $entityId);
			}
			return BT\Status::ERROR;
		}

	}

}