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
	use \Unicity\Throwable;

	/**
	 * This class represents a task decorator.
	 *
	 * @access public
	 * @abstract
	 * @class
	 * @see http://aigamedev.com/open/article/decorator/
	 */
	abstract class Decorator extends BT\Task\Composite {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			$this->tasks->addValue(null);
		}

		/**
		 * This method adds a task to this task composite.
		 *
		 * @access public
		 * @param BT\Task $task the task to be added
		 */
		public function addTask(BT\Task $task) : void {
			$this->tasks->setValue(0, $task);
		}

		/**
		 * This method returns the value of the specified property.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be returned
		 * @return mixed $value                                     the value of the specified property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the property could not
		 *                                                          be set
		 */
		public function __get($name) {
			if ($name == 'task') {
				return $this->tasks->getValue(0);
			}
			else {
				throw new Throwable\InvalidProperty\Exception('Unable to get property. Excepted a valid property name, but got ":name" instead.', array(':name' => $name));
			}
		}

		/**
		 * This method returns the task.
		 *
		 * @access public
		 * @return BT\Task                                          the task
		 */
		public function getTask() : ?BT\Task {
			return $this->tasks->getValue(0);
		}

		/**
		 * This method sets a task to this task decorator.
		 *
		 * @access public
		 * @param BT\Task $task                                     the task to be set
		 */
		public function setTask(?BT\Task $task) {
			$this->tasks->setValue(0, $task);
		}

		/**
		 * This method sets the specified property with the value.
		 *
		 * @access public
		 * @param string $name                                      the name of the property to be set
		 * @param mixed $value                                      the value to set
		 * @throws Throwable\InvalidProperty\Exception              indicates that the property could not
		 *                                                          be set
		 */
		public function __set($name, $value) {
			if ($name == 'task') {
				$this->tasks->setValue(0, $value);
			}
			else {
				throw new Throwable\InvalidProperty\Exception('Unable to set property. Excepted a valid property name, but got ":name" instead.', array(':name' => $name));
			}
		}

	}

}