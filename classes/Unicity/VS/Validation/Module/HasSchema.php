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

namespace Unicity\VS\Validation\Module {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\VS;

	class HasSchema extends VS\Validation\Module {

		public function process(BT\Entity $entity, $other) : int {
			$path = (string) $other;

			$value = $entity->getComponentAtPath($path);

			$schema = $this->policy;

			$expectedType = $this->expectedType($schema);
			$actualType = $this->actualType($path, $schema, $value);

			if ($expectedType !== $actualType) {
				$this->log(VS\Validation\Rule::MALFORMED, "Field must have a type of '{$expectedType}'.", array($path));
				return BT\Status::FAILED;
			}

			$result = true;

			switch ($expectedType) {
				case 'array':
					$result = $this->matchArray($path, $schema, $value);
					break;
				case 'integer':
				case 'number':
					$result = $this->matchNumber($path, $schema, $value);
					break;
				case 'object':
					$result = $this->matchMap($path, $schema, $value);
					break;
				case 'string':
					$result = $this->matchString($path, $schema, $value);
					break;
			}

			return ($result) ? BT\Status::SUCCESS : BT\Status::FAILED;
		}

		/**
		 * This method returns the data type for the value.
		 *
		 * @access protected
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to evaluated
		 * @return string                                           the data type
		 */
		protected function actualType(string $path, array $schema, $value) {
			$actualType = gettype($value);
			$expectedType = $this->expectedType($schema);

			switch ($actualType) {
				case 'double':
					$actualType = 'number';
					break;
				case 'object':
					if ($value instanceof Core\Data\Undefined) {
						return $expectedType;
					}
					if ($value instanceof Common\ArrayList) {
						$actualType = 'array';
					}
					break;
				default:
					$actualType = strtolower($actualType);
					break;
			}

			if ($actualType == 'null') {
				return $expectedType;
			}

			if (($actualType == 'integer') && ($expectedType == 'number')) {
				$this->log(VS\Validation\Rule::SET, "Field type should be '{$expectedType}'.", array($path));
				return $expectedType;
			}

			if ((($actualType == 'integer') || ($actualType == 'number')) && ($expectedType == 'string')) {
				$this->log(VS\Validation\Rule::SET, "Field type should be '{$expectedType}'.", array($path));
				return $expectedType;
			}

			if (($actualType == 'string')) {
				if (($expectedType == 'integer') && preg_match('/^([-]?([0-9]+)$/', $value)) {
					$this->log(VS\Validation\Rule::SET, "Field type should be '{$expectedType}'.", array($path));
					return $expectedType;
				}
				if (($expectedType == 'number') && preg_match('/^([-]?([0-9]+)(\\.[0-9]+)?)?$/', $value)) {
					$this->log(VS\Validation\Rule::SET, "Field type should be '{$expectedType}'.", array($path));
					return $expectedType;
				}
			}

			if (($actualType == 'array') && ($expectedType == 'object')) {
				if (count($value) == 0) {
					return $expectedType;
				}
			}

			return $actualType;
		}

		/**
		 * This method returns the data type expected.
		 *
		 * @access protected
		 * @param array $schema                                     the schema information
		 * @return string                                           the data type
		 */
		protected function expectedType(array $schema) : string {
			$type = $schema['type'] ?? 'object';
			return strtolower($type);
		}

		/**
		 * This method logs the issue.
		 *
		 * @access protected
		 * @param string $rule                                      the type of rule
		 * @param string $message                                   the message describing the issue
		 * @param array $paths                                      the paths associated with the issue
		 */
		protected function log(string $rule, string $message, array $paths) {
			$this->output->addValue([
				'rule' => $rule,
				'message' => $message,
				'paths' => $paths,
			]);
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchArray(string $path, array $schema, $value) {
			if (isset($schema['minItems'])) {
				$size = $value->count();
				if ($size < $schema['minItems']) {
					$minItems = $schema['minItems'];
					$this->log(VS\Validation\Rule::MISMATCH, "Field must have a minimum size of '{$minItems}'.", array($path));
					return false;
				}
			}

			if (isset($schema['maxItems'])) {
				$size = $value->count();
				if ($size > $schema['maxItems']) {
					$maxItems = $schema['maxItems'];
					$this->log(VS\Validation\Rule::MISMATCH, "Field must have a maximum size of '{$maxItems}'.", array($path));
					return false;
				}
			}

			$schema = $schema['items'][0] ?? array();
			if (isset($schema['$ref'])) {
				$schema = ORM\JSON\Model\Helper::resolveJSONSchema($schema);
			}

			return $this->reduce($value, function(bool $carry, array $tuple) use($schema, $path) {
				$i = $tuple[1];
				$v = $tuple[0];

				$ipath = ORM\Query::appendIndex($path, $i);

				$expectedType = $this->expectedType($schema);
				$actualType = $this->actualType($ipath, $schema, $v);

				if ($expectedType !== $actualType) {
					$this->log(VS\Validation\Rule::MALFORMED, "Field must have a type of '{$expectedType}'.", array($ipath));
				}
				else {
					switch ($expectedType) {
						case 'array':
							return $this->matchArray($ipath, $schema, $v) && $carry;
						case 'integer':
						case 'number':
							return $this->matchNumber($ipath, $schema, $v) && $carry;
						case 'object':
							return $this->matchMap($ipath, $schema, $v) && $carry;
						case 'string':
							return $this->matchString($ipath, $schema, $v) && $carry;
					}
				}

				return $carry;
			}, true);
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchMap(string $path, array $schema, $value) {
			if (isset($schema['properties']) && !$value->isEmpty()) {
				$properties = $schema['properties'];

				return $this->reduce($properties, function (bool $carry, array $tuple) use ($value, $path) {
					$k = $tuple[1];
					$v = $value->getValue($k);

					if (Core\Data\ToolKit::isUnset($v)) {
						return $carry;
					}

					$schema = $tuple[0];
					if (isset($schema['$ref'])) {
						$schema = ORM\JSON\Model\Helper::resolveJSONSchema($schema);
					}

					$kpath = ORM\Query::appendKey($path, $k);

					$expectedType = $this->expectedType($schema);
					$actualType = $this->actualType($kpath, $schema, $v);

					if ($expectedType !== $actualType) {
						$this->log(VS\Validation\Rule::MALFORMED, "Field must have a type of '{$expectedType}'.", array($kpath));
					}
					else {
						switch ($expectedType) {
							case 'array':
								return $this->matchArray($kpath, $schema, $v) && $carry;
							case 'integer':
							case 'number':
								return $this->matchNumber($kpath, $schema, $v) && $carry;
							case 'object':
								return $this->matchMap($kpath, $schema, $v) && $carry;
							case 'string':
								return $this->matchString($kpath, $schema, $v) && $carry;
						}
					}

					return $carry;
				}, true);
			}
			return true;
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchNumber(string $path, array $schema, $value) {
			if (isset($schema['enum']) && (count($schema['enum']) > 0)) {
				if (!in_array($value, $schema['enum'])) {
					$this->log(VS\Validation\Rule::MISMATCH, 'Field must be an enumerated constant.', array($path));
					return false;
				}
			}

			if (isset($schema['exclusiveMinimum']) && $schema['exclusiveMinimum']) {
				$minimum = $schema['minimum'] ?? 0;
				if ($value <= $minimum) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has an exclusive minimum value of '{$minimum}'.", array($path));
					return false;
				}
			}

			if (isset($schema['minimum'])) {
				$minimum = $schema['minimum'];
				if ($value < $minimum) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has a minimum value of '{$minimum}'.", array($path));
					return false;
				}
			}

			if (isset($schema['exclusiveMaximum']) && $schema['exclusiveMaximum']) {
				$maximum = $schema['maximum'] ?? PHP_INT_MAX;
				if ($value >= $maximum) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has an exclusive maximum value of '{$maximum}'.", array($path));
					return false;
				}
			}

			if (isset($schema['maximum'])) {
				$maximum = $schema['maximum'];
				if ($value > $maximum) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has a maximum value of '{$maximum}'.", array($path));
					return false;
				}
			}

			if (isset($schema['divisibleBy'])) {
				$divisibleBy = $schema['divisibleBy'];
				if (fmod($value, $divisibleBy) == 0.0) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field must be divisible by '{$divisibleBy}'.", array($path));
					return false;
				}
			}

			return true;
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchString(string $path, array $schema, $value) : bool {
			if (isset($schema['enum']) && (count($schema['enum']) > 0)) {
				if (!in_array($value, $schema['enum'])) {
					$this->log(VS\Validation\Rule::MISMATCH, 'Field must be an enumerated constant.', array($path));
					return false;
				}
			}

			if (isset($schema['pattern'])) {
				$pattern = $schema['pattern'];
				if (!preg_match($pattern, $value)) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field must match pattern '{$pattern}'.", array($path));
					return false;
				}
			}

			if (isset($schema['minLength'])) {
				$minLength = $schema['minLength'];
				if (strlen($value) < $minLength) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has a minimum length of '{$minLength}'.", array($path));
					return false;
				}
			}

			if (isset($schema['maxLength'])) {
				$maxLength = $schema['maxLength'];
				if (strlen($value) > $maxLength) {
					$this->log(VS\Validation\Rule::MISMATCH, "Field has a maximum length of '{$maxLength}'.", array($path));
					return false;
				}
			}

			return true;
		}

		/**
		 * This method performs a reduction on the given collection.
		 *
		 * @access protected
		 * @param mixed $collection                                 the collection to be reduced
		 * @param callable $callback                                the callback
		 * @param mixed $initial                                    the initial value
		 * @return mixed                                            the reduced value
		 */
		protected function reduce($collection, $callback, $initial) {
			$c = $initial;
			foreach ($collection as $k => $v) {
				$c = $callback($c, array($v, $k));
			}
			return $c;
		}

	}

}