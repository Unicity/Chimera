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

namespace Unicity\MappingService\Data {

	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\MappingService;

	/**
	 * This class represents a data model.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package MappingService
	 */
	abstract class Model extends Core\Object implements MappingService\Data\IModel, Config\QueryString\IBuilder {

		/**
		 * This constructor initializes the property to an undefined value.
		 *
		 * @access public
		 * @param mixed $data                                       any data to pre-set
		 */
		public function __construct($data = array()) {
			$properties = get_object_vars($this);
			$data = Core\Convert::toDictionary($data);
			foreach ($properties as $name => $value) {
				$this->$name = array_key_exists($name, $data) ? $data[$name] : Core\Data\Undefined::instance();
			}
		}

		/**
		 * This method maps the properties in the source model with the properties in the target model
		 * as defined in the mapping.  If no mapping is defined, then the properties will be mapped using
		 * their name.
		 *
		 * @access public
		 * @static
		 * @param \Unicity\MappingService\Data\IModel $source       the model that will be the source for
		 *                                                          the mapping
		 * @param \Unicity\MappingService\Data\IModel $target       the model that will be the target for
		 *                                                          the mapping
		 * @param array $mapping                                    an associated array of property names
		 *                                                          where the key is the name of property
		 *                                                          in the source model and the value is
		 *                                                          the name of the property in the target
		 *                                                          model
		 */
		public static function map(MappingService\Data\IModel $source, MappingService\Data\IModel $target, array $mapping = null) {
			if ($mapping !== null) {
				foreach ($mapping as $property1 => $property2) {
					$target->$property2 = $source->$property1;
				}
			}
			else {
				$properties = get_object_vars($source);
				foreach (get_object_vars($target) as $name => $value) {
					if (array_key_exists($name, $properties)) {
						$target->$name = $source->$name;
					}
				}
			}
		}

		/**
		 * This method returns the schema associated with the model's properties.
		 *
		 * @access public
		 * @static
		 * @return array                                            the schema detailing the model's
		 *                                                          properties
		 *
		 * @see http://stackoverflow.com/questions/4262350/how-do-i-get-the-type-of-constructor-parameter-via-reflection
		 */
		public static function schema() {
			$schema = array();
			$class = get_called_class();
			$reflection = new \ReflectionClass($class);
			$properties = $reflection->getProperties();
			foreach ($properties as $property) {
				$comment = $property->getDocComment();
				$matches = array();
				if (preg_match_all('/@var\s+(?P<type>[^\s]+\s*)/ims', $comment, $matches)) {
					$name = $property->getName();

					$type = trim($matches['type'][0]);
					if (($open = strpos($type, '(')) === FALSE) {
						return array(strtoupper($type), 0, 0);
					}
					$close = strpos($type, ')', $open);
					$type = substr($type, 0, $open) . substr($type, $close + 1);

					$schema[$name] = $type;
				}
			}
			return $schema;
		}

		/**
		 * This method returns the data as a query string.
		 *
		 * @access public
		 * @static
		 * @param Config\QueryString\Writer $writer                 the query string writer to be used
		 * @return string                                           the query string
		 */
		public static function toQueryString(Config\QueryString\Writer $writer) {
			$properties = $writer->data;
			$buffer = array();

			foreach ($properties as $key => $value) {
				$kv_pair = (is_array($value))
					? $writer->addArray($key, $value)
					: $writer->addValue($key, $value);

				if ($kv_pair !== null) {
					$buffer[] = $kv_pair;
				}
			}

			return implode('&', $buffer);
		}

	}

}