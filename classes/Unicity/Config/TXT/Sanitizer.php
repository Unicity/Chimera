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

namespace Unicity\Config\TXT {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class defines the contract for sanitizing messages.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Sanitizer extends Config\Sanitizer {

		protected $filters;

		public function __construct($filters) {
			$filters = static::filters($filters);
			$this->filters = array();
			foreach ($filters as $filter) {
				$rule = $filter->hasKey('rule') ? $filter->rule : null;
				if (is_string($rule) && array_key_exists($rule, static::$rules)) {
					$rule = static::$rules[$rule];
				}
				if ($filter->hasKey('keys')) {
					foreach ($filter->keys as $key) {
						$this->filters[] = (object)[
							'pattern' => Core\Convert::toString($key->pattern),
							'rule' => $rule,
						];
					}
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$buffer = new Common\Mutable\StringRef();
			$encoding = $metadata['encoding'] ?? \Unicity\Core\Data\Charset::UTF_8_ENCODING;
			IO\FileReader::read(new IO\StringRef($input), function(IO\FileReader $reader, $line, $index) use ($buffer, $encoding) {
				$line = \Unicity\Core\Data\Charset::encode($line, $encoding, \Unicity\Core\Data\Charset::UTF_8_ENCODING);
				foreach ($this->filters as $filter) {
					$pattern = $filter->pattern;
					$rule = $filter->rule;
					if (is_string($rule) && preg_match('/^mask_last\(([0-9]+)\)$/', $rule, $args)) {
						$line = preg_replace_callback($pattern, function($matches) use ($rule, $args) {
							if (isset($matches[1])) {
								return str_replace($matches[1], Core\Masks::last($matches[1], 'x', $args[1]), $matches[0]);
							}
							return $matches[0];
						}, $line);
					}
					else if (is_callable($rule)) {
						if (preg_match($pattern, $line)) {
							$line = preg_replace_callback($pattern, function($matches) use ($rule) {
								if (isset($matches[1])) {
									return str_replace($matches[1], Core\Convert::toString($rule($matches[1])), $matches[0]);
								}
								return $matches[0];
							}, $line);
						}
					}
					else { // remove
						if (preg_match($pattern, $line)) {
							$line = preg_replace_callback($pattern, function($matches) use ($rule) {
								return '';
							}, $line);
						}
					}
				}
				$buffer->append($line);
			});
			return $buffer->__toString();
		}
	}

}