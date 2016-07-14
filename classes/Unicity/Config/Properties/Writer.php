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

namespace Unicity\Config\Properties {

	use \Unicity\Config;
	use \Unicity\Core;

	/**
	 * This class is used to write a collection to a Java-style properties file.
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
			$this->data = $data;
			$this->metadata = array(
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'eol' => "\n",
				'ext' => '.properties',
				'mime' => 'text/x-java-properties',
				'schema' => array(),
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() {
			$buffer = '';
			foreach ($this->data as $key => $value) {
				$this->addProperty($buffer, Core\Convert::toString($key), $value);
			}
			return $buffer;
		}

		/**
		 * This method recursively adds each entry as a property.
		 *
		 * @access protected
		 * @param string &$buffer                                   the string buffer
		 * @param string $key                                       the key to be used
		 * @param mixed $value                                      the value to be added
		 */
		protected function addProperty(&$buffer, $key, $value) {
			if (!is_array($value)) {
				if (($value == null) || (is_object($value) && ($value instanceof Core\Data\Undefined))) {
					$buffer .= $key . '=' . '';
				}
				else {
					$type = (isset($this->metadata['schema'][$key])) ? $this->metadata['schema'][$key] : 'string';
					$datum = Core\Convert::changeType($value, $type);
					$datum = Core\Convert::toString($datum);
					$datum = Core\Data\Charset::encode($datum, $this->metadata['encoding'][0], $this->metadata['encoding'][1]);
					$buffer .= $key . '=' . $datum;
				}
				$buffer .= $this->metadata['eol'];
			}
			else {
				foreach ($value as $k => $v) {
					$this->addProperty($buffer, $key . '.' . Core\Convert::toString($k), $v);
				}
			}
		}

	}

}