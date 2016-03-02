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
	 * This class represents a task stateful sequence.
	 *
	 * @access public
	 * @class
	 */
	class StatefulSequence extends BT\Task\Sequence {

		/**
		 * This variable stores the last state of the sequence.
		 *
		 * @access public
		 * @var integer
		 */
		protected $state;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
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
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$inactives = 0;
			while ($this->state < $this->tasks->count()) {
				$response = BT\Task\Handler::process($this->tasks->getValue($this->state), $entity);
				$status = $response->getStatus();
				if ($status == BT\Status::INACTIVE) {
					$inactives++;
				}
				else if ($status == BT\Status::ACTIVE) {
					return $response;
				}
				else if ($status != BT\Status::SUCCESS) {
					$this->state = 0;
					return $response;
				}
				$entity = $response->getEntity();
				$this->state++;
			}
			$this->state = 0;
			return ($inactives < $this->tasks->count()) ? BT\State\Success::with($entity) : BT\State\Inactive::with($entity);
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			$this->state = 0;
		}

	}

}