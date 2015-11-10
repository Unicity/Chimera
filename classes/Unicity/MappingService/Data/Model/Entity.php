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

namespace Unicity\MappingService\Data\Model {

	use \Unicity\Core;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	class Entity extends MappingService\Data\Model {

		/**
		 * This constructor initializes the property to an undefined value.
		 *
		 * @access public
		 * @param mixed $data                                       any data to pre-set
		 */
		public function __construct($data = array()) {
			$data = Core\Convert::toDictionary($data);
			$data = array_change_key_case($data, CASE_LOWER);
			foreach (get_object_vars($this) as $name => $value) {
				$this->$name = array_key_exists($name, $data)
					? $data[$name]
					: Core\Data\Undefined::instance();
			}
		}

		/**
		 * This method returns the value associated with the specified property name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value for the specified
		 *                                                          property name
		 */
		public function __get($name) {
			$property = strtolower($name);
			if (property_exists($this, $property)) {
				return $this->$property;
			}
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method sets the value associated with the the specified property name.
		 *
		 * @access public
		 * @param string $name                                      the name of the property
		 * @param mixed $value                                      the value of the property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the property
		 *                                                          does not exist
		 */
		public function __set($name, $value) {
			$property = strtolower($name);
			if (!property_exists($this, $property)) {
				throw new Throwable\InvalidProperty\Exception('Unable to set property. Expected a valid name, but got ":name".', array(':name' => $name));
			}
			$this->$property = $value;
		}

	}

}