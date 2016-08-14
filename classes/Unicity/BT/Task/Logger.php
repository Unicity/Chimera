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
	use \Unicity\Log;

	/**
	 * This class represents a task logger.
	 *
	 * @access public
	 * @class
	 */
	class Logger extends BT\Task\Decorator {

		/**
		 * This variable stores the log level.
		 *
		 * @access protected
		 * @var Log\Level
		 */
		protected $level;

		/**
		 * This variable stores a reference to the log manager.
		 *
		 * @access protected
		 * @var Log\Manager
		 */
		protected $logger;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			$this->level = Log\Level::informational();
			$this->logger = Log\Manager::instance();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->level);
			unset($this->logger);
		}

		/**
		 * This method returns whether the logger is enabled.
		 *
		 * @access public
		 * @return boolean                                          whether the logger is enabled
		 */
		public function isEnabled() {
			if ($this->policy->hasKey('enabled')) {
				return Core\Convert::toBoolean($this->policy->getValue('enabled'));
			}
			return true;
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
			if ($this->isEnabled()) {
				switch ($status) {
					case BT\Status::INACTIVE:
						$this->logger->add($this->level, 'Task: :task Status: Inactive', array(':task' => $this->task));
						break;
					case BT\Status::ACTIVE:
						$this->logger->add($this->level, 'Task: :task Status: Active', array(':task' => $this->task));
						break;
					case BT\Status::SUCCESS:
						$this->logger->add($this->level, 'Task: :task Status: Success', array(':task' => $this->task));
						break;
					case BT\Status::FAILED:
						$this->logger->add($this->level, 'Task: :task Status: Failed', array(':task' => $this->task));
						break;
					case BT\Status::ERROR:
						$this->logger->add($this->level, 'Task: :task Status: Error', array(':task' => $this->task));
						break;
					case BT\Status::QUIT:
						$this->logger->add($this->level, 'Task: :task Status: Quit', array(':task' => $this->task));
						break;
				}
			}
			return $status;
		}

	}

}