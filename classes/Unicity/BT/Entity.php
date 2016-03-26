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

namespace Unicity\BT {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\FP;

	/**
	 * This class represents an entity.
	 *
	 * @access public
	 * @class
	 * @see http://docs.unity3d.com/ScriptReference/GameObject.html
	 */
	class Entity extends Core\Object {

		/**
		 * This variable stores the components associated with this entity.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $component;

		/**
		 * This variables stores the entity's id.
		 *
		 * @access protected
		 * @var string
		 */
		protected $id;

		/**
		 * This variable stores the task id of the tree's root.
		 *
		 * @access protected
		 * @var string
		 */
		protected $taskId;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param string $id                                        the entity's id
		 * @param string $taskId                                    a task id
		 */
		public function __construct(string $id, string $taskId = null) {
			$this->component = new Common\Mutable\HashMap();
			$this->id = $id;
			$this->taskId = $taskId;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->component);
			unset($this->id);
			unset($this->taskId);
		}

		/**
		 * This method returns the component associated with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @return Common\Mutable\ICollection                       the component
		 */
		public function getComponent(string $name) {
			return $this->component->getValue($name);
		}

		/**
		 * This method returns the component associated with the specified name.
		 *
		 * @access public
		 * @param string $type                                      the type of components to be
		 *                                                          returned
		 * @return Common\Mutable\IMap                              the components
		 */
		public function getComponents(string $type = null) {
			if ($type !== null) {
				return FP\IMap::filter($this->component, function($v, $k) use ($type) {
					return ($v instanceof $type);
				});
			}
			return $this->component;
		}

		/**
		 * This method returns entity's id.
		 *
		 * @access public
		 * @return string                                           the entity's id
		 */
		public function getId() {
			return $this->id;
		}

		/**
		 * This method returns a task id assigned to the entity.
		 *
		 * @access public
		 * @return string                                           a task id
		 */
		public function getTaskId() {
			return $this->taskId;
		}

		/**
		 * This method returns whether this entity has a component with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @return boolean                                          whether this entity has the component
		 */
		public function hasComponent(string $name) {
			return $this->component->hasKey($name);
		}

		/**
		 * This method returns whether this entity has all components with the specified names.
		 *
		 * @access public
		 * @param array $names                                      the names of the components
		 * @return boolean                                          whether this entity has all of the
		 *                                                          components
		 */
		public function hasComponents(array $names) {
			if (count($names) > 0) {
				foreach ($names as $name) {
					if (!$this->component->hasKey($name)) {
						return false;
					}
				}
				return true;
			}
			return false;
		}

		/**
		 * This method removes the component associated with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 */
		public function removeComponent(string $name) {
			$this->component->removeKey($name);
		}

		/**
		 * This method removes all components associated with the specified names.
		 *
		 * @access public
		 * @param array $names                                      the names of the components
		 */
		public function removeComponents(array $names) {
			$this->component->removeKeys($names);
		}

		/**
		 * This method sets the component with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @param Common\Mutable\ICollection $component             the component to be set
		 */
		public function setComponent(string $name, Common\Mutable\ICollection $component) {
			$this->component->putEntry($name, $component);
		}

		/**
		 * This method sets the task id of the tree's root.
		 *
		 * @access public
		 * @param string $taskId                                    the task id to be set
		 */
		public function setTaskId(string $taskId = null) {
			$this->taskId = $taskId;
		}

	}

}