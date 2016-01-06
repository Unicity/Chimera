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

namespace Unicity\MappingService\Data\Model {

	use \Unicity\Core;
	use \Unicity\MappingService;

	class Dynamic extends \stdClass implements MappingService\Data\IModel {

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			// do nothing
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
			if (property_exists($this, $name)) {
				return $this->$name;
			}
			return Core\Data\Undefined::instance();
		}

	}

}