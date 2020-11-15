<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;

	/**
	 * This class represents a task error.
	 *
	 * @access public
	 * @class
	 */
	class Error extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('active')) {
				$this->policy->putEntry('acitve', false);
			}
			if (!$this->policy->hasKey('failed')) {
				$this->policy->putEntry('failed', false);
			}
			if (!$this->policy->hasKey('inactive')) {
				$this->policy->putEntry('inactive', false);
			}
			if (!$this->policy->hasKey('success')) {
				$this->policy->putEntry('success', false);
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
			$status = BT\Task\Handler::process($this->task, $engine, $entityId);
			if (($status == BT\Status::ACTIVE) && $this->policy->getValue('active')) {
				return BT\Status::ERROR;
			}
			if (($status == BT\Status::FAILED) && $this->policy->getValue('failed')) {
				return BT\Status::ERROR;
			}
			if (($status == BT\Status::INACTIVE) && $this->policy->getValue('inactive')) {
				return BT\Status::ERROR;
			}
			if (($status == BT\Status::SUCCESS) && $this->policy->getValue('success')) {
				return BT\Status::ERROR;
			}
			return $status;
		}

	}

}