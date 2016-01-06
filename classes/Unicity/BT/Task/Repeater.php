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
	 * This class represents a task repeater.
	 *
	 * @access public
	 * @class
	 * @see http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
	 */
	class Repeater extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if ($this->policy->hasKey('until')) {
				$until = $this->policy->getValue('until');
				if (is_string($until)) {
					$until = BT\Task\Status::valueOf($until);
				}
				if ($until !== BT\Task\Status::SUCCESS) {
					$until = BT\Task\Status::FAILED;
				}
				$this->policy->putEntry('until', $until);
			}
			else {
				$this->policy->putEntry('until', BT\Task\Status::SUCCESS);
			}
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$until = $this->policy->getValue('until');
			do {
				$status = BT\Task\Handler::process($this->task, $exchange);
				if (!in_array($status, array(BT\Task\Status::SUCCESS, BT\Task\Status::FAILED))) {
					return $status;
				}
			}
			while ($status == $until);
			return $until;
		}

	}

}