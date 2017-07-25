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

namespace Unicity\Log\CSV {

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

		public function __construct(IO\File $file) {
			$config = Common\Collection::useCollections(Config\JSON\Reader::load($file)->read());
			$this->entries = array();
			foreach ($config['rules'] as $rule) {
				$filter = $rule['filter'];
				foreach ($rule['fields'] as $field) {
					$this->entries[] = [
						'filter' => $filter,
						'column_name' => $field['column_name'],
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$records = Config\CSV\Reader::load($input, $metadata)->read();
			foreach ($records as $record) {
				foreach ($this->entries as $entry) {
					$filter = $entry['filter'];
					$column_name = $entry['column_name'];
					if ($filter !== null) {
						if (isset($record->$column_name)) {
							$record->$column_name = $filter($record->$column_name);
						}
					}
					else {
						// TODO remove value if filter is null
					}
				}
			}
			return new IO\StringRef($input->getBytes());
		}

	}

}