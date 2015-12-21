<?php

/**
 * Copyright 2015 Unicity International
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
	 * This class represents a task limiter.
	 *
	 * @access public
	 * @class
	 */
	class Limiter extends BT\Task\Decorator {

		/**
		 * This variable stores the number of calls that have been made to this task.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $calls;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('limit')) {
				$this->policy->putEntry('limit', 1);
			}
			$this->calls = 0;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->calls);
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$limit = Core\Convert::toInteger($this->policy->getValue('limit'));
			if ($this->calls < $limit) {
				$status = BT\Task\Handler::process($this->task, $exchange);
				$this->calls++;
				return $status;
			}
			return BT\Task\Status::FAILED;
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			$this->calls = 0;
		}

	}

}