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

		protected $rows;

		public function __construct(IO\File $file) {
			$config = Common\Collection::useCollections(Config\JSON\Reader::load($file)->read());
			$this->rows = array();
			foreach ($config['rules'] as $rule) {
				$filter = $rule['filter'];
				foreach ($rule['fields'] as $field) {
					$index = (int) $field['row_index'];
					$this->rows[$index][] = [
						'filter' => $filter,
						'offset' => $field['column_offset'],
						'length' => $field['column_length'],
					];
				}
			}
			ksort($this->rows);
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$buffer = new Common\Mutable\StringRef();
			IO\FileReader::read($input, function(IO\FileReader $reader, $line, $index) use ($buffer) {
				if (isset($this->rows[$index])) {
					$entries = $this->rows[$index];
					foreach ($entries as $entry) {
						$filter = $entry['filter'];
						$offset = $entry['offset'];
						$length = $entry['length'];
						if ($filter !== null) {
							$line = substr_replace($line, str_pad($filter(substr($line, $offset, $length)), $length, ' '), $offset, $length);
						}
						else {
							$line = substr_replace($line, str_pad('', $length, ' '), $offset, $length);
						}
					}
					$buffer->append($line);
				}
			});
			return new IO\StringRef($buffer);
		}

	}

}