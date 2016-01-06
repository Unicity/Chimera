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
	 * This class represents a task gambler.
	 *
	 * @access public
	 * @class
	 * @see http://php.net/manual/en/function.rand.php
	 * @see http://php.net/manual/en/function.mt-rand.php
	 */
	class Gambler extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('callable')) {
				$this->policy->putEntry('callable', 'rand'); // ['rand', 'mt_rand']
			}
			if (!$this->policy->hasKey('odds')) {
				$this->policy->putEntry('odds', 0.01);
			}
			if (!$this->policy->hasKey('options')) {
				$this->policy->putEntry('options', 100);
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
			$callable = explode(',', $this->policy->getValue('callable'));
			$options = Core\Convert::toInteger($this->policy->getValue('options'));
			$probability = Core\Convert::toDouble($this->policy->hasKey('odds')) * $options;
			if (call_user_func($callable, array(1, $options)) <= $probability) {
				$status = BT\Task\Handler::process($this->task, $exchange);
				return $status;
			}
			return BT\Task\Status::ACTIVE;
		}

	}

}