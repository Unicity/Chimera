<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011 Spadefoot Team
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

namespace Unicity\Spring\Object {

	use \Unicity\Core;

	/**
	 * This class describes a SpringXML object's properties.
	 *
	 * @access public
	 * @class
	 * @package Spring
	 *
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/config/BeanDefinition.html
	 */
	class Definition extends Core\AbstractObject {

		/**
		 * This variable stores the object's definition (i.e. attributes).
		 *
		 * @access protected
		 * @var array
		 */
		protected $definition;

		/**
		 * This constructor initializes the class with the specified definition.
		 *
		 * @access public
		 * @param array $definition                                 the object's definition
		 */
		public function __construct(array $definition) {
			$this->definition = $definition;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->definition);
		}

		/**
		 * This method returns the name of the factory method assigned to create this object.
		 *
		 * @access public
		 * @return string                                           the name of the factory method
		 */
		public function getFactoryMethod() {
			return (isset($this->definition['factory-method'])) ? $this->definition['factory-method'] : '';
		}

		/**
		 * This method returns the name of the factory object to call the factory method on.
		 *
		 * @access public
		 * @return string                                           the name of the factory object
		 */
		public function getFactoryObject() {
			return (isset($this->definition['factory-object'])) ? str_replace('.', '\\', $this->definition['factory-object']) : '';
		}

		/**
		 * This method returns the object's id associated with the object.
		 *
		 * @access public
		 * @return string                                           the object's id
		 */
		public function getId() {
			return $this->definition['id'];
		}

		/**
		 * This method returns the names associated with the object.
		 *
		 * @access public
		 * @return array                                            the names associated with the object
		 */
		public function getNames() {
			return (isset($this->definition['name'])) ? preg_split('/(,|;|\s)+/', $this->definition['name']) : array();
		}

		/**
		 * This method returns the object's scope.
		 *
		 * @access public
		 * @return string                                           the object's scope
		 */
		public function getScope() {
			return (isset($this->definition['scope'])) ? $this->definition['scope'] : 'singleton';
		}

		/**
		 * This method returns the object's type.
		 *
		 * @access public
		 * @return string                                           the object's type
		 */
		public function getType() {
			return (isset($this->definition['type'])) ? str_replace('.', '\\', $this->definition['type']) : '';
		}

		/**
		 * This method returns whether the object's scope has been defined as a prototype.
		 *
		 * @access public
		 * @return boolean                                          whether the object is defined
		 *                                                          as a prototype
		 */
		public function isPrototype() {
			return ($this->getScope() == 'prototype');
		}

		/**
		 * This method returns whether the object's scope has been defined as a singleton.
		 *
		 * @access public
		 * @return boolean                                          whether the object is defined
		 *                                                          as a singleton
		 */
		public function isSingleton() {
			return ($this->getScope() == 'singleton');
		}

	}

}