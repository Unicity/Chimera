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

	/**
	 * This class represents a task error.
	 *
	 * @access public
	 * @class
	 */
	class Error extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			if (!$this->policy->hasKey('active')) {
				$this->policy->putEntry('acitve', false);
			}
			if (!$this->policy->hasKey('failed')) {
				$this->policy->putEntry('failed', false);
			}
			if (!$this->policy->hasKey('inactive')) {
				$this->policy->putEntry('inactive', false);
			}
			if (!$this->policy->hasKey('success')) {
				$this->policy->putEntry('success', false);
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
			$state = BT\Task\Handler::process($this->task, $entity);
			if (($state instanceof BT\State\Active) && $this->policy->getValue('active')) {
				return BT\State\Error::with($state->getEntity());
			}
			if (($state instanceof BT\State\Failed) && $this->policy->getValue('failed')) {
				return BT\State\Error::with($state->getEntity());
			}
			if (($state instanceof BT\State\Inactive) && $this->policy->getValue('inactive')) {
				return BT\State\Error::with($state->getEntity());
			}
			if (($state instanceof BT\State\Success) && $this->policy->getValue('success')) {
				return BT\State\Error::with($state->getEntity());
			}
			return $state;
		}

	}

}