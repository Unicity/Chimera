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
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$inactives = 0;
			while ($this->state < $this->tasks->count()) {
				$status = BT\Task\Handler::process($this->tasks->getValue($this->state), $exchange);
				if (in_array($status, array(BT\Task\Status::SUCCESS, BT\Task\Status::ERROR, BT\Task\Status::QUIT))) {
					$this->state = 0;
					return $status;
				}
				else if ($status == BT\Task\Status::ACTIVE) {
					return $status;
				}
				else if ($status == BT\Task\Status::INACTIVE) {
					$inactives++;
				}
				$this->state++;
			}
			$this->state = 0;
			return ($inactives < $this->tasks->count()) ? BT\Task\Status::FAILED : BT\Task\Status::INACTIVE;
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