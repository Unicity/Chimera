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
					$this->filters[] = (object) [
						'name' => $key->name,
						'rule' => $rule,
					];
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$input = static::input($input);
			$records = Common\Collection::useCollections(Config\CSV\Reader::load($input, $metadata)->read());
			foreach ($records as $record) {
				foreach ($this->filters as $filter) {
					$rule = $filter->rule;
					$name = $filter->name;
					if ($record->hasKey($name)) {
						$record->$name = is_callable($rule) ? $rule($record->$name) : '';
					}
				}
			}
			$writer = new Config\CSV\Writer($records);
			$writer->config($metadata);
			return $writer->render();
		}

	}

}