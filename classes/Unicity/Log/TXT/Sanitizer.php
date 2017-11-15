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

namespace Unicity\Log\TXT {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Log;
	use \Unicity\Throwable;

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
				foreach ($filter->keys as $key) {
					$this->filters[] = (object) [
						'pattern' => Core\Convert::toString($key->pattern),
						'rule' => $rule,
						'type' => $key->hasKey('type') ? strtolower(Core\Convert::toString($key->type)) : 'simple',
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : string {
			$buffer = new Common\Mutable\StringRef();
			IO\FileReader::read($input, function(IO\FileReader $reader, $line, $index) use ($buffer) {
				foreach ($this->filters as $filter) {
					$rule = $filter->rule;
					if (is_callable($rule)) {
						switch ($filter->type) {
							case 'complex':
								$pattern = $filter->pattern;
								if (preg_match($pattern, $line)) {
									$replacement = preg_replace_callback($pattern, function($matches) use ($rule) {
										return '${1}' . Core\Convert::toString($rule($matches[1])) . '${2}';
									}, $line);
									$line = preg_replace($pattern, $replacement, $line);
								}
								break;
							case 'simple':
								$separator = '(?:%[A-Za-z0-9]{1,2}|\W|\s)';
								$pattern = '#(' . $separator . '+' . $filter->pattern . $separator . '+' . ')[a-zA-Z0-9\-]+(?!>)(?=' . $separator . ')#i';
								if (preg_match($pattern, $line)) {
									$replacement = preg_replace_callback($pattern, function($matches) use ($rule) {
										return '${1}' . Core\Convert::toString($rule($matches[0]));
									}, $line);
									$line = preg_replace($pattern, $replacement, $line);
								}
								break;
						}
					}
					else {
						// TODO remove value if filter is null
					}
				}
				$buffer->append($line);
			});
			return $buffer->__toString();
		}
	}

}