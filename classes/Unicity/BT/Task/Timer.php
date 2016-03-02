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
	 * @see http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
	 * @see http://stackoverflow.com/questions/535020/tracking-the-script-execution-time-in-php
	 */
	class Timer extends BT\Task\Decorator {

		/**
		 * This variable stores the time of when the timer started.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $start_time;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('delay')) {
				$this->policy->putEntry('delay', 0); // 1 millisecond = 1/1000 of a second
			}
			if (!$this->policy->hasKey('duration')) {
				$this->policy->putEntry('duration', 1000); // 1 millisecond = 1/1000 of a second
			}
			$this->start_time = microtime(true);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->start_time);
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$delay = Core\Convert::toInteger($this->policy->getValue('delay')) / 1000; // milliseconds => seconds

			$deltaT = microtime(true) - $this->start_time;
			if ($deltaT >= $delay) {
				$duration = Core\Convert::toInteger($this->policy->getValue('duration')) / 1000; // milliseconds => seconds

				if ($deltaT < ($delay + $duration)) {
					return BT\Task\Handler::process($this->task, $exchange);
				}
			}

			return BT\Status::INACTIVE;
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			$this->start_time = microtime(true);
		}

	}

}