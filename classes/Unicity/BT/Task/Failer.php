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
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $settings                     any settings associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $settings = null) {
			parent::__construct($blackboard, $settings);
			if (!$this->settings->hasKey('error')) {
				$this->settings->putEntry('error', false);
			}
			if (!$this->settings->hasKey('inactive')) {
				$this->settings->putEntry('inactive', false);
			}
			if (!$this->settings->hasKey('active')) {
				$this->settings->putEntry('acitve', false);
			}
			if (!$this->settings->hasKey('success')) {
				$this->settings->putEntry('success', true);
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
			$status = BT\Task\Handler::process($this->task, $exchange);
			switch ($status) {
				case BT\Task\Status::ERROR:
					if ($this->settings->getValue('error')) {
						return BT\Task\Status::FAILED;
					}
					return $status;
				case BT\Task\Status::INACTIVE:
					if ($this->settings->getValue('inactive')) {
						return BT\Task\Status::FAILED;
					}
					return $status;
				case BT\Task\Status::ACTIVE:
					if ($this->settings->getValue('active')) {
						return BT\Task\Status::FAILED;
					}
					return $status;
				case BT\Task\Status::SUCCESS:
					if ($this->settings->getValue('success')) {
						return BT\Task\Status::FAILED;
					}
					return $status;
			}
			return $status;
		}

	}

}