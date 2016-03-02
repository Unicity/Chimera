<?php

/**
 * Copyright 2015-2016 Unicity International
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task parallel.
	 *
	 * @access public
	 * @class
	 * @see http://aigamedev.com/open/article/parallel/
	 */
	class Parallel extends BT\Task\Branch {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			// frequency: once, each
			// order: shuffle, weight, fixed
			if (!$this->policy->hasKey('shuffle')) {
				$this->policy->putEntry('shuffle', false);
			}
			if (!$this->policy->hasKey('successes')) {
				$this->policy->putEntry('successes', 1);
			}
			if (!$this->policy->hasKey('failures')) {
				$this->policy->putEntry('failures', 1);
			}
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$count = $this->tasks->count();
			if ($count > 0) {
				$shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
				if ($shuffle) {
					$this->tasks->shuffle();
				}
				$inactivesCt = 0;
				$successesCt = 0;
				$successesMax = min(Core\Convert::toInteger($this->policy->getValue('successes')), $count);
				$failuresCt = 0;
				$failuresMax = min(Core\Convert::toInteger($this->policy->getValue('failures')), $count);
				foreach ($this->tasks as $task) {
					$state = BT\Task\Handler::process($task, $entity);
					switch ($state->getStatus()) {
						case BT\Status::INACTIVE:
							$inactivesCt++;
							break;
						case BT\Status::ACTIVE:
							break;
						case BT\Status::SUCCESS:
							$successesCt++;
							if ($successesCt >= $successesMax) {
								return $state;
							}
							break;
						case BT\Status::FAILED:
							$failuresCt++;
							if ($failuresCt >= $failuresMax) {
								return $state;
							}
							break;
						case BT\Status::ERROR:
						case BT\Status::QUIT:
							return $state;
					}
					$entity = $state->getEntity();
				}
				if ($inactivesCt != $count) {
					return BT\State\Active::with($entity);
				}
			}
			return BT\State\Inactive::with($entity);
		}

	}

}