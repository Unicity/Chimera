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

namespace Unicity\VLD\Parser\Module {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\VLD;
	use \Unicity\VLD\Parser\RuleType;

	class MatchesSchema extends VLD\Parser\Module {

		public function process(BT\Entity $entity, array $paths) : VLD\Parser\Feedback {
			$feedback = new VLD\Parser\Feedback();

			$schema = $this->policy;

			foreach ($paths as $path) {
				$value = $entity->getComponentAtPath($path);

				if (isset($schema['required']) && boolval($schema['required']) && Core\Data\ToolKit::isUndefined($value)) {
					$feedback->addViolation(RuleType::missing(), [$path], 'value.required');
				}
				else {
					$expectedType = $this->expectedType($schema);
					$actualType = $this->actualType($feedback, $path, $schema, $value);

					if ($expectedType !== $actualType) {
						$feedback->addViolation(RuleType::malformed(), [$path], 'value.compare.type', ['{{type}}' => $expectedType]);
					}
					else {
						switch ($expectedType) {
							case 'array':
								$this->matchArray($feedback, $path, $schema, $value);
								break;
							case 'integer':
							case 'number':
								$this->matchNumber($feedback, $path, $schema, $value);
								break;
							case 'object':
								$this->matchMap($feedback, $path, $schema, $value);
								break;
							case 'string':
								$this->matchString($feedback, $path, $schema, $value);
								break;
						}
					}
				}
			}

			return $feedback;
		}

		/**
		 * This method returns the data type for the value.
		 *
		 * @access protected
		 * @param VLD\Parser\Feedback $feedback                     the feedback buffer
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to evaluated
		 * @return string                                           the data type
		 */
		protected function actualType(VLD\Parser\Feedback $feedback, string $path, array $schema, $value) {
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
				if (isset($schema['nullable']) && !$schema['nullable']) {
					return $actualType;
				}
				return $expectedType;
			}

			if (($actualType == 'integer') && ($expectedType == 'number')) {
				$feedback->addRecommendation(RuleType::set(), [$path => Core\Convert::changeType($value, $expectedType)], 'value.retype', ['{{type}}' => $expectedType]);
				return $expectedType;
			}

			if ((($actualType == 'integer') || ($actualType == 'number')) && ($expectedType == 'string')) {
				$feedback->addRecommendation(RuleType::set(), [$path => Core\Convert::changeType($value, $expectedType)], 'value.retype', ['{{type}}' => $expectedType]);
				return $expectedType;
			}

			if (($actualType == 'string')) {
				if (($expectedType == 'integer') && preg_match('/^([-]?([0-9]+)$/', $value)) {
					$feedback->addRecommendation(RuleType::set(), [$path => Core\Convert::changeType($value, $expectedType)], 'value.retype', ['{{type}}' => $expectedType]);
					return $expectedType;
				}
				if (($expectedType == 'number') && preg_match('/^([-]?([0-9]+)(\\.[0-9]+)?)?$/', $value)) {
					$feedback->addRecommendation(RuleType::set(), [$path => Core\Convert::changeType($value, $expectedType)], 'value.retype', ['{{type}}' => $expectedType]);
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
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param VLD\Parser\Feedback $feedback                     the feedback buffer
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchArray(VLD\Parser\Feedback $feedback, string $path, array $schema, $value) {
			if (isset($schema['minItems'])) {
				$size = $value->count();
				if ($size < $schema['minItems']) {
					$minItems = $schema['minItems'];
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.ge.size', ['{{size}}' => $minItems]);
					return false;
				}
			}

			if (isset($schema['maxItems'])) {
				$size = $value->count();
				if ($size > $schema['maxItems']) {
					$maxItems = $schema['maxItems'];
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.le.size', ['{{size}}' => $maxItems]);
					return false;
				}
			}

			$schema = $schema['items'][0] ?? array();
			if (isset($schema['$ref'])) {
				$schema = ORM\JSON\Model\Helper::resolveJSONSchema($schema);
			}

			return $this->reduce($value, function(bool $carry, array $tuple) use($feedback, $schema, $path) {
				$i = $tuple[1];
				$v = $tuple[0];

				$ipath = ORM\Query::appendIndex($path, $i);

				if (isset($schema['required']) && boolval($schema['required']) && Core\Data\ToolKit::isUndefined($v)) {
					$feedback->addViolation(RuleType::missing(), [$path], 'value.required');
				}
				else {
					$expectedType = $this->expectedType($schema);
					$actualType = $this->actualType($feedback, $ipath, $schema, $v);

					if ($expectedType !== $actualType) {
						$feedback->addViolation(RuleType::malformed(), [$ipath], 'value.compare.type', ['{{type}}' => $expectedType]);
					}
					else {
						switch ($expectedType) {
							case 'array':
								return $this->matchArray($feedback, $ipath, $schema, $v) && $carry;
							case 'integer':
							case 'number':
								return $this->matchNumber($feedback, $ipath, $schema, $v) && $carry;
							case 'object':
								return $this->matchMap($feedback, $ipath, $schema, $v) && $carry;
							case 'string':
								return $this->matchString($feedback, $ipath, $schema, $v) && $carry;
						}
					}
				}

				return $carry;
			}, true);
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param VLD\Parser\Feedback $feedback                     the feedback buffer
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchMap(VLD\Parser\Feedback $feedback, string $path, array $schema, $value) {
			if (isset($schema['properties']) && !$value->isEmpty()) {
				$properties = $schema['properties'];

				return $this->reduce($properties, function (bool $carry, array $tuple) use ($feedback, $value, $path) {
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

					if (isset($schema['required']) && boolval($schema['required']) && Core\Data\ToolKit::isUndefined($v)) {
						$feedback->addViolation(RuleType::missing(), [$path], 'value.required');
					}
					else {
						$expectedType = $this->expectedType($schema);
						$actualType = $this->actualType($feedback, $kpath, $schema, $v);

						if ($expectedType !== $actualType) {
							$feedback->addViolation(RuleType::malformed(), [$kpath], 'value.compare.type', ['{{type}}' => $expectedType]);
						}
						else {
							switch ($expectedType) {
								case 'array':
									return $this->matchArray($feedback, $kpath, $schema, $v) && $carry;
								case 'integer':
								case 'number':
									return $this->matchNumber($feedback, $kpath, $schema, $v) && $carry;
								case 'object':
									return $this->matchMap($feedback, $kpath, $schema, $v) && $carry;
								case 'string':
									return $this->matchString($feedback, $kpath, $schema, $v) && $carry;
							}
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
		 * @param VLD\Parser\Feedback $feedback                     the feedback buffer
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchNumber(VLD\Parser\Feedback $feedback, string $path, array $schema, $value) {
			if (isset($schema['enum']) && (count($schema['enum']) > 0)) {
				$enum = $schema['enum'];
				if (!in_array($value, $enum)) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.enum', ['{{enum}}' => implode(':', $enum)]);
					return false;
				}
			}

			if (isset($schema['exclusiveMinimum']) && $schema['exclusiveMinimum']) {
				$minimum = $schema['minimum'] ?? 0;
				if ($value <= $minimum) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.gt', ['{{value}}' => $minimum]);
					return false;
				}
			}

			if (isset($schema['minimum'])) {
				$minimum = $schema['minimum'];
				if ($value < $minimum) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.ge', ['{{value}}' => $minimum]);
					return false;
				}
			}

			if (isset($schema['exclusiveMaximum']) && $schema['exclusiveMaximum']) {
				$maximum = $schema['maximum'] ?? PHP_INT_MAX;
				if ($value >= $maximum) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.lt', ['{{value}}' => $maximum]);
					return false;
				}
			}

			if (isset($schema['maximum'])) {
				$maximum = $schema['maximum'];
				if ($value > $maximum) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.le', ['{{value}}' => $maximum]);
					return false;
				}
			}

			if (isset($schema['divisibleBy'])) {
				$divisibleBy = $schema['divisibleBy'];
				if (fmod($value, $divisibleBy) == 0.0) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.divisibility', ['{{value}}' => $divisibleBy]);
					return false;
				}
			}

			return true;
		}

		/**
		 * This method returns whether the value complies with its field's constraints.
		 *
		 * @access protected
		 * @param VLD\Parser\Feedback $feedback                     the feedback buffer
		 * @param string $path                                      the current path
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to be evaluated
		 * @return boolean                                          whether the value complies
		 */
		protected function matchString(VLD\Parser\Feedback $feedback, string $path, array $schema, $value) : bool {
			if (isset($schema['enum']) && (count($schema['enum']) > 0)) {
				$enum = $schema['enum'];
				if (!in_array($value, $enum)) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.enum', ['{{enum}}' => implode(':', $enum)]);
					return false;
				}
			}

			if (isset($schema['pattern'])) {
				$pattern = $schema['pattern'];
				if (!preg_match($pattern, $value)) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.regex', ['{{regex}}' => $pattern]);
					return false;
				}
			}

			if (isset($schema['minLength'])) {
				$minLength = $schema['minLength'];
				if (strlen($value) < $minLength) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.ge.length', ['{{length}}' => $minLength]);
					return false;
				}
			}

			if (isset($schema['maxLength'])) {
				$maxLength = $schema['maxLength'];
				if (strlen($value) > $maxLength) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.le.length', ['{{length}}' => $maxLength]);
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