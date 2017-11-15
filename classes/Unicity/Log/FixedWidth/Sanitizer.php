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

		public function __construct($config) {
			$config = Log\Sanitizer::loadConfig($config);
			$this->filters = array();
			foreach ($config->filters as $filter) {
				$delegate = $filter->hasKey('delegate') ? $filter->delegate : null;
				foreach ($filter->rules as $rule) {
					$row_index = Core\Convert::toInteger($rule->row_index);
					$this->filters[$row_index][] = (object) [
						'delegate' => $delegate,
						'column_offset' => Core\Convert::toInteger($rule->column_offset),
						'column_length' => Core\Convert::toInteger($rule->column_length),
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$buffer = new Common\Mutable\StringRef();
			IO\FileReader::read($input, function(IO\FileReader $reader, $line, $index) use ($buffer) {
				if (isset($this->filters[$index])) {
					foreach ($this->filters[$index] as $filter) {
						$delegate = $filter->delegate;
						$offset = $filter->column_offset;
						$length = $filter->column_length;
						if (is_callable($delegate)) {
							$line = substr_replace($line, str_pad($delegate(substr($line, $offset, $length)), $length, ' '), $offset, $length);
						}
						else {
							$line = substr_replace($line, str_repeat(' ', $length), $offset, $length);
						}
					}
				}
				$buffer->append($line);
			});
			return new IO\StringRef($buffer);
		}

	}

}