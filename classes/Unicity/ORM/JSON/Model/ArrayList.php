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
	class ArrayList extends Common\Mutable\ArrayList implements ORM\IModel {

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
		 * This method will add the value specified.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be added
		 * @return boolean                                          whether the value was added
		 */
		public function addValue($value) {
			if (isset($this->schema['items'][0])) {
				$definition = $this->schema['items'][0];
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
			}
			$this->elements[] = $value;
			return true;
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
		 * This method returns the element at the the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element
		 * @return mixed                                            the element at the specified index
		 * @throws Throwable\InvalidArgument\Exception              indicates that an index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that the index is out of bounds
		 */
		public function getValue($index) {
			if (is_integer($index)) {
				if (array_key_exists($index, $this->elements)) {
					return $this->elements[$index];
				}
				else if ($index == $this->count()) {
					if (isset($this->schema['items'][0])) {
						$definition = $this->schema['items'][0];

						if (isset($definition['type'])) {
							switch ($definition['type']) {
								case 'array':
									$value = new ORM\JSON\Model\ArrayList($definition, $this->case_sensitive);
									$this->elements[$index] = $value;
									return $value;
								case 'object':
									$value = new ORM\JSON\Model\HashMap($definition, $this->case_sensitive);
									$this->elements[$index] = $value;
									return $value;
							}
						}

						if (isset($definition['default'])) {
							$value = $definition['default'];
							$this->elements[$index] = $value;
							return $value;
						}
					}

					$value = Core\Data\Undefined::instance();
					$this->elements[$index] = $value;
					return $value;
				}
				return Core\Data\Undefined::instance();
			}
			throw new Throwable\InvalidArgument\Exception('Unable to get element. ":type" is of the wrong data type.', array(':type' => Core\DataType::info($index)->type));
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
		 * This method inserts a value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index where the value will be inserted at
		 * @param mixed $value                                      the value to be inserted
		 * @return boolean                                          whether the value was inserted
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 * @throws Throwable\OutOfBounds\Exception                  indicates that no value exists at the
		 *                                                          specified index
		 *
		 * @see http://www.justin-cook.com/wp/2006/08/02/php-insert-into-an-array-at-a-specific-position/
		 */
		public function insertValue(int $index, $value) : bool {
			$count = $this->count();
			if (($index >= 0) && ($index < $count)) {
				array_splice($this->elements, $index, 0, array($value));
				return true;
			}
			else if ($index == $count) {
				$this->addValue($value);
				return true;
			}
			throw new Throwable\OutOfBounds\Exception('Unable to insert value. Invalid index specified', array(':index' => $index, ':value' => $value));
		}

		/**
		 * This method returns whether this model is case sensitive.
		 *
		 * @access public
		 * @return boolean                                          whether this model is case sensitive
		 */
		public function isCaseSensitive() : bool {
			return $this->case_sensitive;
		}

		/**
		 * This method replaces the value at the specified index.
		 *
		 * @access public
		 * @param integer $index                                    the index of the element to be set
		 * @param mixed $value                                      the value to be set
		 * @return boolean                                          whether the value was set
		 * @throws Throwable\InvalidArgument\Exception              indicates that index must be an integer
		 */
		public function setValue($index, $value) {
			if (is_integer($index)) {
				if (array_key_exists($index, $this->elements)) {
					if (isset($this->schema['items'][0])) {
						$definition = $this->schema['items'][0];
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
					}
					$this->elements[$index] = $value;
					return true;
				}
				else if ($index == $this->count()) {
					$this->addValue($value);
					return true;
				}
				return false;
			}
			throw new Throwable\InvalidArgument\Exception('Unable to set element. Expected an integer, but got ":index" of type ":type".', array(':index' => $index, ':type' => Core\DataType::info($index)->type));
		}

	}

}