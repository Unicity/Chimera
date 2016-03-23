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

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task ticker.
	 *
	 * @access public
	 * @class
	 * @see http://cc.byexamples.com/2007/03/14/create-a-timer/
	 * @see http://stackoverflow.com/questions/12783737/how-to-use-setinterval-in-php
	 */
	class Ticker extends BT\Task\Decorator {

		/**
		 * This variable stores the time of when the ticker will tick next.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $next_time;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('interval')) {
				$this->policy->putEntry('interval', 1000); // 1 millisecond = 1/1000 of a second
			}
			$this->next_time = microtime(true) + (Core\Convert::toInteger($this->policy->getValue('interval')) / 1000);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->next_time);
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param integer $entityId                                 the entity id being processed
		 * @param BT\Application $application                       the application running
		 * @return integer                                          the status
		 */
		public function process(int $entityId, BT\Application $application) {
			$interval = Core\Convert::toInteger($this->policy->getValue('interval')) / 1000; // milliseconds => seconds

			if (microtime(true) >= $this->next_time) {
				$status = BT\Task\Handler::process($this->task, $entityId, $application);
				$this->next_time += $interval;
				return $status;
			}

			return BT\Status::ACTIVE;
		}

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			$this->start_time = microtime(true) + (Core\Convert::toInteger($this->policy->getValue('interval')) / 1000);
		}

	}

}