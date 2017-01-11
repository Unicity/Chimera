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

namespace Unicity\ORM\JSON\Model {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	/**
	 * This class will build a model using a pre-defined JSON schema.
	 *
	 * @access public
	 * @class
	 * @package ORM
	 *
	 * @see http://json-schema.org/
	 */
	class HashMap extends Common\Mutable\HashMap implements ORM\IModel {

		/**
		 * This variable stores whether field names are case sensitive.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $case_sensitive;

		/**
		 * This variable stores the model's schema.
		 *
		 * @access protected
		 * @var array
		 */
		protected $schema;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param mixed $schema                                     the JSON schema
		 * @param boolean $case_sensitive                           whether field names are case
		 *                                                          sensitive
		 */
		public function __construct($schema, bool $case_sensitive = true) {
			parent::__construct();
			$this->schema = ORM\JSON\Model\Helper::resolveJSONSchema($schema);
			$this->case_sensitive = Core\Convert::toBoolean($case_sensitive);
		}

		/**
		 * This method returns an array of arguments for constructing another collection
		 * via function programming.
		 *
		 * @access public
		 * @return array                                            the argument array for initialization
		 */
		public function __constructor_args() : array {
			return array($this->schema, $this->case_sensitive);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->schema);
			unset($this->case_sensitive);
		}

		/**
		 * This method processes the key for use by the hash map.
		 *
		 * @access protected
		 * @param mixed $key                                        the key to be processed
		 * @return string                                           the key to be used
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates an invalid key was specified
		 */
		protected function getKey($key) {
			$key = parent::getKey($key);
			if (!$this->case_sensitive) {
				$key = strtolower($key);
			}
			return $key;
		}

		/**
		 * This method returns the schema associated with this model.
		 *
		 * @access public
		 * @return array                                            the model's schema
		 */
		public function getSchema() {
			return $this->schema;
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @override
		 * @param mixed $key                                        the key of the value to be returned
		 * @return mixed                                            the element associated with the specified key
		 * @throws Throwable\InvalidArgument\Exception              indicates that key is not a scaler type
		 * @throws Throwable\KeyNotFound\Exception                  indicates that key could not be found
		 */
		public function getValue($key) {
			$field = $this->getKey($key);
			if (isset($this->schema['properties'][$field])) {
				if (array_key_exists($field, $this->elements)) {
					return $this->elements[$field];
				}

				$definition = $this->schema['properties'][$field];

				if (isset($definition['type'])) {
					switch ($definition['type']) {
						case 'array':
							$value = new ORM\JSON\Model\ArrayList($definition, $this->case_sensitive);
							$this->elements[$field] = $value;
							return $value;
						case 'object':
							$value = new ORM\JSON\Model\HashMap($definition, $this->case_sensitive);
							$this->elements[$field] = $value;
							return $value;
					}
				}

				if (isset($definition['default'])) {
					$value = $definition['default'];
					$this->elements[$field] = $value;
					return $value;
				}

				$value = Core\Data\Undefined::instance();
				$this->elements[$field] = $value;
				return $value;
			}
			else {
				throw new Throwable\KeyNotFound\Exception('Unable to get element. Key ":key" does not exist.', array(':key' => $key));
			}
		}

		/**
		 * This method returns whether this model is case sensitive.
		 *
		 * @access public
		 * @return boolean                                          whether this model is case sensitive
		 */
		public function isCaseSensitive() {
			return $this->case_sensitive;
		}

		/**
		 * This method puts the key/value mapping to the collection.
		 *
		 * @access public
		 * @override
		 * @param mixed $key                                        the key to be mapped
		 * @param mixed $value                                      the value to be mapped
		 * @return boolean                                          whether the key/value pair was set
		 */
		public function putEntry($key, $value) {
			try {
				if (Core\Data\Undefined::instance()->__equals($value)) {
					return $this->removeKey($key);
				}
				else {
					$key = $this->getKey($key);
					if (isset($this->schema['properties'][$key])) {
						$definition = $this->schema['properties'][$key];
						if (isset($definition['type'])) {
							switch ($definition['type']) {
								case 'array':
									$value = ORM\JSON\Model\Helper::resolveArrayValue($value, $definition, $this->case_sensitive);
									break;
								case 'boolean':
									$value = ORM\JSON\Model\Helper::resolveBooleanValue($value, $definition);
									break;
								case 'integer':
									$value = ORM\JSON\Model\Helper::resolveIntegerValue($value, $definition);
									break;
								case 'number':
									$value = ORM\JSON\Model\Helper::resolveNumberValue($value, $definition);
									break;
								case 'null':
									$value = ORM\JSON\Model\Helper::resolveNullValue($value, $definition);
									break;
								case 'object':
									$value = ORM\JSON\Model\Helper::resolveObjectValue($value, $definition, $this->case_sensitive);
									break;
								case 'string':
									$value = ORM\JSON\Model\Helper::resolveStringValue($value, $definition);
									break;
							}
						}
						$this->elements[$key] = $value;
						return true;
					}
					return false;
				}
			}
			catch (\Exception $ex) {
				return false;
			}
		}

		/**
		 * This method will rename a key.
		 *
		 * @access public
		 * @override
		 * @param mixed $old                                        the key to be renamed
		 * @param mixed $new                                        the name of the new key
		 * @throws Throwable\UnimplementedMethod\Exception          indicates the method has not been
		 *                                                          implemented
		 */
		public function renameKey($old, $new) {
			throw new Throwable\UnimplementedMethod\Exception('Invalid call to member function. Method has not been implemented.');
		}

	}

}