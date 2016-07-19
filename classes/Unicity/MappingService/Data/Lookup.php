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

namespace Unicity\MappingService\Data {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\MappingService;

	/**
	 * This class represents a lookup action.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package MappingService
	 */
	abstract class Lookup extends Core\Object {

		/**
		 * This constructor initializes the class.
		 *
		 * @access protected
		 */
		protected function __construct() {
			// do nothing
		}

		/**
		 * This method performs any pre-processing of the data.
		 *
		 * @access protected
		 * @param Common\HashMap $data                              the data to be processed
		 * @return Common\HashMap                                   the processed data
		 */
		protected function before(Common\HashMap $data) {
			return $data;
		}

		/**
		 * This method performs any processing of the data.
		 *
		 * @access protected
		 * @abstract
		 * @param Common\HashMap $data                              the data to be processed
		 * @return Common\HashMap                                   the processed data
		 */
		protected abstract function process(Common\HashMap $data);

		/**
		 * This method performs any post-processing of the data.
		 *
		 * @access protected
		 * @param Common\HashMap $data                              the data to be processed
		 * @return Common\HashMap                                   the processed data
		 */
		protected function after(Common\HashMap $data) {
			return $data;
		}

		/**
		 * This method executes the lookup.
		 *
		 * @access public
		 * @static
		 * @final
		 * @param Common\HashMap $data                              the data to be processed
		 * @return Common\HashMap                                   the processed data
		 */
		public static final function execute(Common\HashMap $data) {
			$object = new static();
			$data = $object->before($data);
			$data = $object->process($data);
			$data = $object->after($data);
			return $data;
		}

	}

}