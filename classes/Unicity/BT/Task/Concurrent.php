<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task concurrent.
	 *
	 * @access public
	 * @class
	 */
	class Concurrent extends BT\Task\Parallel {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('successes')) {
				$this->policy->putEntry('successes', 1);
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
			$count = $this->tasks->count();
			if ($count > 0) {
				$activesCt = 0;
				$successesCt = 0;
				$failuresCt = 0;
				foreach ($this->tasks as $task) {
					$status = BT\Task\Handler::process($task, $engine, $entityId);
					switch ($status) {
						case BT\Status::INACTIVE:
							break;
						case BT\Status::ACTIVE:
							$activesCt++;
							break;
						case BT\Status::SUCCESS:
							$successesCt++;
							break;
						case BT\Status::FAILED:
							$failuresCt++;
							break;
						case BT\Status::ERROR:
						case BT\Status::QUIT:
							return $status;
					}
				}
				$successesMax = min(Core\Convert::toInteger($this->policy->getValue('successes')), $count);
				if ($successesCt >= $successesMax) {
					return BT\Status::SUCCESS;
				}
				if ($failuresCt > 0) {
					return BT\Status::FAILED;
				}
				if ($activesCt > 0) {
					return BT\Status::ACTIVE;
				}
			}
			return BT\Status::INACTIVE;
		}

	}

}