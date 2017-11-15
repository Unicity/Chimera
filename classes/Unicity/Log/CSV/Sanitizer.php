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
					$this->filters[] = (object) [
						'delegate' => $delegate,
						'column_name' => Core\Convert::toString($rule->column_name),
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : string {
			$records = Common\Collection::useCollections(Config\CSV\Reader::load($input, $metadata)->read());
			foreach ($records as $record) {
				foreach ($this->filters as $filter) {
					$delegate = $filter->delegate;
					$column_name = $filter->column_name;
					if ($record->hasKey($column_name)) {
						$record->$column_name = is_callable($delegate) ? $delegate($record->$column_name) : '';
					}
				}
			}
			$writer = new Config\CSV\Writer($records);
			$writer->config($metadata);
			return $writer->render();
		}

	}

}