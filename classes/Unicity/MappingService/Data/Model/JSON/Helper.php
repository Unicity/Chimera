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

namespace Unicity\MappingService\Data\Model\JSON {

	use \Unicity\Bootstrap;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	/**
	 * This class will build a model using a pre-defined JSON schema.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 *
	 */
	class Helper extends Core\Object {

		/**
		 * This method attempts to resolve the value as an array in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @param boolean $case_sensitive                           whether field names are case
		 *                                                          sensitive
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveArrayValue($value, $definition, $case_sensitive) {
			if (Core\Data\ToolKit::isUnset($value)) {
				if (isset($definition['required']) && $definition['required']) {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is an array, but got ":type".', array(':type' => Core\DataType::info($value)->type));
				}
				return $value;
			}

			if (!($value instanceof MappingService\Data\Model\JSON\ArrayList)) {
				if (is_object($value) || is_array($value)) {
					$model = new MappingService\Data\Model\JSON\ArrayList($definition, $case_sensitive);
					$model->addValues(Core\Convert::toArray($value));
					$value = $model;
				}
				else {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array of type ":type0", but got a value of type ":type1".', array(':type0' => '\\Unicity\\MappingService\\Data\\Model\\JSON\\ArrayList', ':type1' => Core\DataType::info($value)->type));
				}
			}

			if (isset($definition['minItems'])) {
				$size = $value->count();
				if ($size < $definition['minItems']) {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array that has a minimum size of ":minItems", but got ":size".', array(':minItems' => $definition['minItems'], ':size' => $size));
				}
			}

			if (isset($definition['maxItems'])) {
				$size = $value->count();
				if ($size > $definition['maxItems']) {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array that has a maximum size of ":maxItems", but got ":size".', array(':maxItems' => $definition['maxItems'], ':size' => $size));
				}
			}

			return $value;
		}

		/**
		 * This method attempts to resolve the value as a boolean in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveBooleanValue($value, $definition) {
			return ORM\JSON\Model\Helper::resolveBooleanValue($value, $definition);
		}

		/**
		 * This method returns the JSON schema for the specified input.
		 *
		 * @access public
		 * @static
		 * @param mixed $schema                                     the JSON schema to be loaded
		 * @return array                                            the JSON schema
		 */
		public static function resolveJSONSchema($schema) {
			return ORM\JSON\Model\Helper::resolveJSONSchema($schema);
		}

		/**
		 * This method attempts to resolve the value as an integer in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveIntegerValue($value, $definition) {
			return ORM\JSON\Model\Helper::resolveIntegerValue($value, $definition);
		}

		/**
		 * This method attempts to resolve the value as a number in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveNumberValue($value, $definition) {
			return ORM\JSON\Model\Helper::resolveNumberValue($value, $definition);
		}

		/**
		 * This method attempts to resolve the value as a null in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveNullValue($value, $definition) {
			return ORM\JSON\Model\Helper::resolveNullValue($value, $definition);
		}

		/**
		 * This method attempts to resolve the value as an object in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @param boolean $case_sensitive                           whether field names are case
		 *                                                          sensitive
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveObjectValue($value, $definition, $case_sensitive) {
			if (Core\Data\ToolKit::isUnset($value)) {
				if (isset($definition['required']) && $definition['required']) {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is an object, but got :type.', array(':type' => Core\DataType::info($value)->type));
				}
				return $value;
			}

			if (!($value instanceof MappingService\Data\Model\JSON\HashMap)) {
				if (is_object($value) || is_array($value)) {
					$model = new MappingService\Data\Model\JSON\HashMap($definition, $case_sensitive);
					$model->putEntries(Core\Convert::toDictionary($value));
					$value = $model;
				}
				else {
					throw new Throwable\Runtime\Exception('Invalid value defined. Expected an object of type ":type0", but got a value of type ":type1".', array(':type0' => '\\Unicity\\MappingService\\Data\\Model\\JSON\\HashMap', ':type1' => Core\DataType::info($value)->type));
				}
			}

			return $value;
		}

		/**
		 * This method attempts to resolve the value as a string in accordance with the schema
		 * definition.
		 *
		 * @access public
		 * @static
		 * @param mixed $value                                      the value to be resolved
		 * @param array $definition                                 the schema definition
		 * @return mixed                                            the resolved value
		 * @throws Throwable\Runtime\Exception                      indicates that the value failed
		 *                                                          to meet a requirement
		 */
		public static function resolveStringValue($value, $definition) {
			return ORM\JSON\Model\Helper::resolveStringValue($value, $definition);
		}

	}

}