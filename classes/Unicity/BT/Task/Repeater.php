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
				$until = (is_string($until))
					? BT\Status::valueOf(strtoupper($until))
					: Core\Convert::toInteger($until);
				if ($until !== BT\Status::SUCCESS) {
					$until = BT\Status::FAILED;
				}
				$this->policy->putEntry('until', $until);
			}
			else {
				$this->policy->putEntry('until', BT\Status::SUCCESS);
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
			$until = $this->policy->getValue('until');
			do {
				$state = BT\Task\Handler::process($this->task, $entity);
				$status = $state->getStatus();
				if (!in_array($status, array(BT\Status::SUCCESS, BT\Status::FAILED))) {
					return $state;
				}
			}
			while ($status == $until);
			return $state;
		}

	}

}