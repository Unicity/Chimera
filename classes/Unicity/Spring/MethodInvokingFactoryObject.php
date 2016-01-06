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

namespace Unicity\Spring {

	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	/**
	 * This class is used to process PHP objects into Spring XML.
	 *
	 * @access public
	 * @class
	 * @package Spring
	 *
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/util/MethodInvoker.html
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/config/MethodInvokingBean.html
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/config/MethodInvokingFactoryBean.html
	 * @see https://gist.github.com/bulain/1139874
	 */
	class MethodInvokingFactoryObject extends Core\Object implements Spring\InitializingObject, Spring\FactoryObject {

		/**
		 * This variable stores any arguments associated with the call.
		 *
		 * @access public
		 * @var array
		 */
		public $arguments;

		/**
		 * This variable stores the callable to be called.
		 *
		 * @access protected
		 * @var callable
		 */
		protected $callable;

		/**
		 * This variable stores the object created by the call.
		 *
		 * @access protected
		 * @var object
		 */
		protected $object;

		/**
		 * This variable stores the name of the class to be called.
		 *
		 * @access public
		 * @var string
		 */
		public $targetClass;

		/**
		 * This variable stores the name of the method to be called.
		 *
		 * @access public
		 * @var string
		 */
		public $targetMethod;

		/**
		 * This variable stores the object to be called.
		 *
		 * @access public
		 * @var object
		 */
		public $targetObject;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			// do nothing
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			unset($this->targetClass);
			unset($this->targetObject);
			unset($this->targetMethod);
			unset($this->arguments);
			unset($this->callable);
			unset($this->object);
		}

		/**
		 * This method is called object's properties have been set.
		 *
		 * @access public
		 */
		public function afterPropertiesSet() {
			if ($this->targetClass !== null) {
				if (!method_exists($this->targetClass, $this->targetMethod)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid callable, but got ":class" and ":method".', array(':class' => $this->targetClass, ':method' => $this->targetMethod));
				}
				$this->callable = array($this->targetClass, $this->targetMethod);
			}
			else if ($this->targetClass !== null) {
				if (!method_exists($this->targetObject, $this->targetMethod)) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid callable, but got ":class" and ":method".', array(':class' => Core\DataType::info($this->targetObject)->type, ':method' => $this->targetMethod));
				}
				$this->callable = array($this->targetObject, $this->targetMethod);
			}
			else {
				if (in_array($this->targetMethod, array('eval'))) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid callable, but got ":method".', array(':method' => $this->targetMethod));
				}
				$this->callable = $this->targetMethod;
			}
		}

		/**
		 * This method returns the object created.
		 *
		 * @access public
		 * @return object
		 */
		public function getObject() {
			if ($this->object === null) {
				$this->object = call_user_func($this->callable, $this->arguments);
			}
			return $this->object;
		}

	}

}