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
	 * This class represents a task timer.
	 *
	 * @access public
	 * @class
	 * @see https://en.wikipedia.org/wiki/Semaphore_%28programming%29
	 */
	class Semaphore extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('id')) {
				$this->policy->putEntry('id', __CLASS__);
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
			$id = Core\Convert::toString($this->policy->getValue('id'));

			if ($this->blackboard->hasKey($id)) {
				$hashCode = $this->blackboard->getValue($id);
				if ($hashCode == $this->task->__hashCode()) {
					$state = BT\Task\Handler::process($this->task, $entity);
					if ($state->getStatus() != BT\Status::ACTIVE) {
						$this->blackboard->removeKey($id);
					}
					return $state;
				}
				return BT\State\Active::with($entity);
			}
			else {
				$state = BT\Task\Handler::process($this->task, $entity);
				if ($state->getStatus() == BT\Status::ACTIVE) {
					$this->blackboard->putEntry($id, $this->task->__hashCode());
				}
				return $state;
			}
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			$id = Core\Convert::toString($this->policy->getValue('id'));
			if ($this->blackboard->hasKey($id)) {
				$this->blackboard->removeKey($id);
			}
		}

	}

}