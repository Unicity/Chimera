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
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			// frequency: once, each
			// order: shuffle, weight, fixed
			if (!$this->policy->hasKey('shuffle')) {
				$this->policy->putEntry('shuffle', false);
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
			$shuffle = Core\Convert::toBoolean($this->policy->getValue('shuffle'));
			if ($shuffle) {
				$this->tasks->shuffle();
			}
			$inactives = 0;
			foreach ($this->tasks as $task) {
				$status = BT\Task\Handler::process($task, $engine, $entityId);
				if (in_array($status, array(BT\Status::ACTIVE, BT\Status::SUCCESS, BT\Status::ERROR, BT\Status::QUIT))) {
					return $status;
				}
				if ($status == BT\Status::INACTIVE) {
					$inactives++;
				}
			}
			return ($inactives < $this->tasks->count()) ? BT\Status::FAILED : BT\Status::INACTIVE;
		}

	}

}