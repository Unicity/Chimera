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

namespace Unicity\Log\FixedWidth {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Log;

	/**
	 * This class defines the contract for sanitizing messages.
	 *
	 * @access public
	 * @class
	 * @package Log
	 */
	class Sanitizer extends Log\Sanitizer {

		protected $filters;

		public function __construct($filters) {
			$filters = static::filters($filters);
			$this->filters = array();
			foreach ($filters as $filter) {
				$rule = $filter->hasKey('rule') ? $filter->rule : null;
				if (is_string($rule) && array_key_exists($rule, static::$rules)) {
					$rule = static::$rules[$rule];
				}
				foreach ($filter->keys as $key) {
					$index = Core\Convert::toInteger($key->index);
					$this->filters[$index][] = (object) [
						'length' => Core\Convert::toInteger($key->length),
						'offset' => Core\Convert::toInteger($key->offset),
						'rule' => $rule,
					];
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$input = static::input($input);
			$buffer = new Common\Mutable\StringRef();
			IO\FileReader::read($input, function(IO\FileReader $reader, $line, $index) use ($buffer) {
				if (isset($this->filters[$index])) {
					foreach ($this->filters[$index] as $filter) {
						$rule = $filter->rule;
						$offset = $filter->offset;
						$length = $filter->length;
						if (is_callable($rule)) {
							$line = substr_replace($line, str_pad($rule(substr($line, $offset, $length)), $length, ' '), $offset, $length);
						}
						else {
							$line = substr_replace($line, str_repeat(' ', $length), $offset, $length);
						}
					}
				}
				$buffer->append($line);
			});
			return $buffer->__toString();
		}

	}

}