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
	use \Unicity\ORM;

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
		protected $components;

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
		 * @param array $properties                                 the default properties
		 */
		public function __construct(array $properties) {
			$this->components = $properties['components'] ?? new Common\Mutable\HashMap();
			$this->id = $properties['entity_id'];
			$this->taskId = $properties['task_id'] ?? null;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->components);
			unset($this->id);
			unset($this->taskId);
		}

		/**
		 * This method returns the component associated with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @return mixed                                            the component
		 */
		public function getComponent(string $name) {
			return $this->components->getValue($name);
		}

		/**
		 * This method returns the component at the specified path.
		 *
		 * @access public
		 * @param string $path                                      the path to the component
		 * @return mixed                                            the component
		 */
		public function getComponentAtPath(string $path) {
			if ($path === '@') {
				return $this->components;
			}
			return ORM\Query::getValue($this->components, $path);
		}

		/**
		 * This method returns the component's path for the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @return string                                           the component's path
		 */
		public function getComponentPath(string $name) : string {
			return ORM\Query::getPath($this->components, $name);
		}

		/**
		 * This method returns the component associated with the specified name.
		 *
		 * @access public
		 * @param callable $predicate                               a predicate for determining which
		 *                                                          components to return
		 * @return Common\Mutable\IMap                              the components
		 */
		public function getComponents(callable $predicate = null) : Common\Mutable\IMap {
			if ($predicate !== null) {
				return FP\IMap::filter($this->components, $predicate);
			}
			return $this->components;
		}

		/**
		 * This method returns entity's id.
		 *
		 * @access public
		 * @return string                                           the entity's id
		 */
		public function getId() : string {
			return $this->id;
		}

		/**
		 * This method returns a task id assigned to the entity.
		 *
		 * @access public
		 * @return string                                           a task id
		 */
		public function getTaskId() : ?string {
			return $this->taskId;
		}

		/**
		 * This method returns whether this entity has a component with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @return boolean                                          whether this entity has the component
		 */
		public function hasComponent(string $name) : bool {
			return $this->components->hasKey($name);
		}

		/**
		 * This method returns whether this entity has a component at the specified path.
		 *
		 * @access public
		 * @param string $path                                      the path to the component
		 * @return boolean                                          whether this entity has the component
		 */
		public function hasComponentAtPath(string $path) : bool {
			if ($path === '@') {
				return !Core\Data\ToolKit::isUndefined($this->components);
			}
			return ORM\Query::hasPath($this->components, $path);
		}

		/**
		 * This method returns whether this entity has all components with the specified names.
		 *
		 * @access public
		 * @param array $names                                      the names of the components
		 * @return boolean                                          whether this entity has all of the
		 *                                                          components
		 */
		public function hasComponents(array $names) : bool {
			if (count($names) > 0) {
				foreach ($names as $name) {
					if (!$this->components->hasKey($name)) {
						return false;
					}
				}
				return true;
			}
			return false;
		}

		/**
		 * This method notifies the entity with a message using the specified handler.
		 *
		 * @access public
		 * @param callable $handler                                 the handler to be called
		 * @param mixed $message                                    the message to be passed
		 */
		public function notify(callable $handler, $message = null) : void {
			$handler($this, $message);
		}

		/**
		 * This method removes the component associated with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 */
		public function removeComponent(string $name) : void {
			$this->components->removeKey($name);
		}

		/**
		 * This method removes all components associated with the specified names.
		 *
		 * @access public
		 * @param array $names                                      the names of the components
		 */
		public function removeComponents(array $names) : void {
			$this->components->removeKeys($names);
		}

		/**
		 * This method sets the component with the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the component
		 * @param mixed $component                                  the component to be set
		 */
		public function setComponent(string $name, $component) : void {
			$this->components->putEntry($name, $component);
		}

		/**
		 * This method sets the component at the specified path.
		 *
		 * @access public
		 * @param string $path                                      the path to the component
		 * @param mixed $component                                  the component to be set
		 */
		public function setComponentAtPath(string $path, $component) : void {
			ORM\Query::setValue($this->components, $path, $component);
		}

		/**
		 * This method sets the task id of the tree's root.
		 *
		 * @access public
		 * @param string $taskId                                    the task id to be set
		 */
		public function setTaskId(?string $taskId) : void {
			$this->taskId = $taskId;
		}

	}

}