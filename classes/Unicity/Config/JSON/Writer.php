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

namespace Unicity\Config\JSON {

	use \Unicity\Config;

	/**
	 * This class is used to write a collection to a JSON file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'ext' => '.json',
				'mime' => 'application/json',
				'prefix' => '',
				'pretty' => false,
				'suffix' => '',
				'uri' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() {
			$prefix = (isset($this->metadata['prefix'])) ? $this->metadata['prefix'] : '';
			$suffix = (isset($this->metadata['suffix'])) ? $this->metadata['suffix'] : '';
			$options = 0;
			if (isset($this->metadata['pretty']) && $this->metadata['pretty']) {
				$options |= JSON_PRETTY_PRINT;
			}
			return $prefix . json_encode($this->data, $options) . $suffix;
		}

	}

}