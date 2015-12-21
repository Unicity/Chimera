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
	 * This class represents a task composite.
	 *
	 * @access public
	 * @abstract
	 * @class
	 */
	abstract class Composite extends BT\Task {

		/**
		 * This variable stores the tasks making up the task composite.
		 *
		 * @access protected
		 * @var Common\Mutable\IList
		 */
		protected $tasks;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			parent::__construct($blackboard, $policy);
			$this->tasks = new Common\Mutable\ArrayList();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->tasks);
		}

		/**
		 * This method adds a task to this task composite.
		 *
		 * @access public
		 * @param BT\Task $task                                     the task to be added
		 */
		public function addTask(BT\Task $task) {
			$this->tasks->addValue($task);
		}

		/**
		 * This method adds a list of tasks to this task composite.
		 *
		 * @access public
		 * @param Common\Mutable\IList $tasks                       a list of tasks to be added
		 */
		public function addTasks(Common\Mutable\IList $tasks) {
			foreach ($tasks as $task) {
				$this->addTask($task);
			}
		}

		/**
		 * This method returns the task.
		 *
		 * @access public
		 * @return Common\Mutable\IList                             the tasks associated with this task
		 *                                                          composite
		 */
		public function getTasks() {
			return $this->tasks;
		}

		/**
		 * This method removes the specified task from this task composite.
		 *
		 * @access public
		 * @param BT\Task $task                                     the task to be removed
		 */
		public function removeTask(BT\Task $task) {
			$this->tasks->removeValue($task);
		}

		/**
		 * This method removes all tasks in this task composite.
		 *
		 * @access public
		 */
		public function removeTasks() {
			$this->tasks->clear();
		}

	}

}