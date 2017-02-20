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

namespace Unicity\VS\Parser\Task {

	use \Unicity\Common;
	use \Unicity\Core;
	use Unicity\VS;

	class HasSchema implements VS\Parser\Task {

		public function test2($path, $schema) : bool {
			$value = VS\Parser\Context::instance()->current()->getComponentAtPath($path);

			if (isset($schema['type'])) {
				$expectedType = $this->expectedType($schema);
				$acutualType = $this->actualType($schema, $value);
				if ($expectedType !== $acutualType) {
					$this->send(Rule::MALFORMED, $this->message($expectedType), $path);
					var_dump($expectedType, $acutualType);
				}
			}

			return true;
		}

		/**
		 * This method returns the data type for the value.
		 *
		 * @access protected
		 * @param array $schema                                     the schema information
		 * @param mixed $value                                      the value to evaluated
		 * @return string                                           the data type
		 */
		protected function actualType($schema, $value) {
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
				// TODO report "Set" message
				return $expectedType;
			}

			if ((($actualType == 'integer') || ($actualType == 'number')) && ($expectedType == 'string')) {
				// TODO report "Set" message
				return $expectedType;
			}

			if (($actualType == 'string')) {
				if (($expectedType == 'integer') && preg_match('/^([-]?([0-9]+)$/', $value)) {
					// TODO report "Set" message
					return $expectedType;
				}
				if (($expectedType == 'number') && preg_match('/^([-]?([0-9]+)(\\.[0-9]+)?)?$/', $value)) {
					// TODO report "Set" message
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
		protected function expectedType($schema) {
			return strtolower($schema['type']);
		}

		/**
		 * This method returns the log message.
		 *
		 * @access protected
		 * @param string $type                                      the data type
		 * @return string                                           the log message
		 */
		protected function message(string $type) {
			if (in_array($type[0], array('a', 'e', 'i', 'o', 'u'))) {
				return "Field requires an '{$type}'.";
			}
			return "Field requires a '{$type}'.";
		}

	}

}