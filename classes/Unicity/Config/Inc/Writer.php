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

namespace Unicity\Config\Inc {

	use \Unicity\Config;
	use \Unicity\Core;

	/**
	 * This class is used to write a collection to a PHP-include file.
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
				'ext' => '.php',
				'mime' => 'text/html',
				'uri' => null,
			);
		}

		/**
		 * This method serializes a key for an array.
		 *
		 * @access protected
		 * @param mixed $key                                        the key to be serialized
		 * @return string                                           the serialized key
		 */
		protected function getSerializedKey($key) {
			if (is_string($key)) {
				return "'" . addslashes($key) . "'";
			}
			return $key;
		}

		/**
		 * This method serializes a value.
		 *
		 * @access protected
		 * @param mixed $value                                      the value to be serialized
		 * @return string                                           the serialized key
		 */
		protected function getSerializedValue($value) {
			$type = gettype($value);
			switch ($type) {
				case 'array':
					$array  = 'array(' . $this->metadata['eol'];
					foreach ($value as $k => $v) {
						$array .= $this->getSerializedKey($k) . ' => ' . $this->getSerializedValue($v) . ',' . $this->metadata['eol'];
					}
					$array .= ')';
					return $array;
				case 'NULL':
					return 'null';
				case 'object':
					if ($value instanceof Core\Data\Undefined) {
						return '\\Unicity\\Core\\Data\\Undefined::instance()';
					}
					return 'null';
				case 'boolean':
				case 'double':
				case 'integer':
					return Core\Convert::toString($value);
				case 'string':
				case 'resource':
				case 'unknown type':
				default:
					$value = Core\Convert::toString($value);
					$value = Core\Data\Charset::encode($value, $this->metadata['encoding'][0], $this->metadata['encoding'][1]);
					return "'" . addslashes($value) . "'";
			}
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() {
			$buffer  = '<?php' . $this->metadata['eol'] . $this->metadata['eol'];
			ob_start();
			echo $this->getSerializedValue($this->data);
			$contents = ob_get_contents();
			ob_end_clean();
			$buffer .= 'return ' . $contents . ';' . $this->metadata['eol'];
			return $buffer;
		}

	}

}