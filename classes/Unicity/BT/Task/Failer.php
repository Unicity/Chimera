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

	/**
	 * This class represents a task failer.
	 *
	 * @access public
	 * @class
	 * @see http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
	 */
	class Failer extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('error')) {
				$this->policy->putEntry('error', false);
			}
			if (!$this->policy->hasKey('inactive')) {
				$this->policy->putEntry('inactive', false);
			}
			if (!$this->policy->hasKey('active')) {
				$this->policy->putEntry('acitve', false);
			}
			if (!$this->policy->hasKey('success')) {
				$this->policy->putEntry('success', true);
			}
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) {
			$status = BT\Task\Handler::process($this->task, $engine, $entityId);
			if (($status == BT\Status::ACTIVE) && $this->policy->getValue('active')) {
				return BT\Status::FAILED;
			}
			if (($status == BT\Status::ERROR) && $this->policy->getValue('error')) {
				return BT\Status::FAILED;
			}
			if (($status == BT\Status::INACTIVE) && $this->policy->getValue('inactive')) {
				return BT\Status::FAILED;
			}
			if (($status == BT\Status::SUCCESS) && $this->policy->getValue('success')) {
				return BT\Status::FAILED;
			}
			return $status;
		}

	}

}