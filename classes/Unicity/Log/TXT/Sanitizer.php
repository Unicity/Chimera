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

		protected $entries;

		public function __construct($file) {
			$config = Common\Collection::useCollections(Config\JSON\Reader::load($file)->read());
			$this->entries = array();
			foreach ($config['rules'] as $rule) {
				$filter = $rule['filter'];
				foreach ($rule['fields'] as $field) {
					$this->entries[] = [
						'filter' => $filter,
						'pattern' => $field['pattern'],
						'type' => isset($field['type']) ? strtolower($field['type']) : 'simple',
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$buffer = new Common\Mutable\StringRef();
			IO\FileReader::read($input, function(IO\FileReader $reader, $line, $index) use ($buffer) {
				foreach ($this->entries as $entry) {
					$filter = $entry['filter'];
					$pattern = $entry['pattern'];
					$type = $entry['type'];
					if ($filter !== null) {
						switch ($type) {
							case 'complex':
								$line = preg_replace($pattern, function($matches) use ($filter) {
									return $filter($matches[1]);
								}, $line);
								break;
							case 'simple':
								$separator = '(?:%[A-Za-z0-9]{1,2}|\W|\s)';
								$search = '#(' . $separator . '+' . $pattern . $separator . '+' . ')[a-zA-Z0-9\-]+(?!>)(?=' . $separator . ')#i';
								$line = preg_replace($search, function($matches) use ($filter) {
									return $filter($matches[1]);
								}, $line);
								break;
						}
					}
					else {
						// TODO remove value if filter is null
					}
				}
				$buffer->append($line);
			});
			return new IO\StringRef($buffer);
		}
	}

}