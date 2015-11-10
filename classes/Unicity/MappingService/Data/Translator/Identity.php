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

namespace Unicity\MappingService\Data\Translator {

	use \Unicity\Core;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	/**
	 * This class is used to translate a data model using data translations.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class Identity extends MappingService\Data\Translator {

		/**
		 * This method attempts to call the specified getter/setter.
		 *
		 * @access public
		 * @param string $method                                    the name of the method to be
		 *                                                          called
		 * @param array $args                                       the arguments passed
		 * @return mixed                                            the result of the method
		 */
		public function __call($method, $args) {
			if (preg_match('/^get[_a-zA-Z0-9]+$/', $method)) {
				$properties = preg_split('/_+/', substr($method, 3));
				$key = null;
				$value = $this->models;
				foreach ($properties as $property) {
					$key = $property;
					if (!property_exists($value, $key)) {
						return null;
					}
					$value = $value->$key;
				}
				$field = new MappingService\Data\Field(MappingService\Data\FormatType::canonical());
				if (is_object($value)) {
					$properties = get_object_vars($value);
					foreach ($properties as $key => $value) {
						$field->putItem($key, $value);
					}
				}
				else {
					$field->putItem($key, $value);
				}
				return $field;
			}
			return null;
		}

		/**
		 * This method returns whether the specified method exists.
		 *
		 * @access public
		 * @param string $method                                    the name of the method
		 * @return boolean                                          whether the specified method exists
		 */
		public function __hasMethod($method) {
			if (parent::__hasMethod($method)) {
				return true;
			}
			else if (preg_match('/^get[_a-zA-Z0-9]+$/', $method)) {
				$properties = preg_split('/_+/', substr($method, 3));
				$value = $this->models;
				foreach ($properties as $key) {
					if (!property_exists($value, $key)) {
						return false;
					}
					$value = $value->$key;
				}
				return true;
			}
			return false;
		}

	}

}