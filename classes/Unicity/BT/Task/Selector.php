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
	 * This class represents a task selector.
	 *
	 * @access public
	 * @class
	 * @see http://aigamedev.com/open/article/selector/
	 */
	class Selector extends BT\Task\Branch {

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
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
			if ($shuffle) {
				$this->tasks->shuffle();
			}
			$inactives = 0;
			foreach ($this->tasks as $task) {
				$status = BT\Task\Handler::process($task, $exchange);
				if (in_array($status, array(BT\Task\Status::ACTIVE, BT\Task\Status::SUCCESS, BT\Task\Status::ERROR, BT\Task\Status::QUIT))) {
					return $status;
				}
				else if ($status == BT\Task\Status::INACTIVE) {
					$inactives++;
				}
			}
			return ($inactives < $this->tasks->count()) ? BT\Task\Status::FAILED : BT\Task\Status::INACTIVE;
		}

	}

}