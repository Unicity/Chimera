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
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
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
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$until = $this->policy->getValue('until');
			do {
				$status = BT\Task\Handler::process($this->task, $engine, $entityId);
				if (!in_array($status, array(BT\Status::SUCCESS, BT\Status::FAILED))) {
					return $status;
				}
			}
			while ($status == $until);
			return $status;
		}

	}

}