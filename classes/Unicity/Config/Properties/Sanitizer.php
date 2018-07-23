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

namespace Unicity\Config\Properties {

	use \Unicity\Config;
	use \Unicity\Core;

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
							'path' => $key->path,
							'rule' => $rule,
						];
					}
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$record = Config\Properties\Helper::unmarshal($input, $metadata);
			foreach ($this->filters as $filter) {
				$rule = $filter->rule;
				$path = $filter->path;
				if (is_string($rule) && preg_match('/^whitelist\((.+)\)$/', $rule, $matches)) { // removes all other fields not in the whitelist
					$fields = array_map('trim', explode(',', $matches[1]));
					foreach ($record as $key => $val) {
						$prefix = "{$path}.";
						$strlen = strlen($prefix);
						if (!strncmp($key, $prefix, $strlen)) {
							$suffix = substr($key, $strlen);
							if (!in_array($suffix, $fields)) {
								$record->$path = Core\Data\Undefined::instance();
							}
						}
					}
				}
				else if (is_string($rule) && preg_match('/^blacklist\((.+)\)$/', $rule, $matches)) { // removes the specified fields in the blacklist
					$fields = array_map('trim', explode(',', $matches[1]));
					foreach ($record as $key => $val) {
						$prefix = "{$path}.";
						$strlen = strlen($prefix);
						if (!strncmp($key, $prefix, $strlen)) {
							$suffix = substr($key, $strlen);
							if (in_array($suffix, $fields)) {
								$record->$path = Core\Data\Undefined::instance();
							}
						}
					}
				}
				else if ($record->hasKey($path)) {
					if (is_string($rule) && preg_match('/^mask_last\(([0-9]+)\)$/', $rule, $matches)) {
						$record->$path = Core\Masks::last($record->$path, 'x', $matches[1]);
					}
					else if (is_callable($rule)) {
						$record->$path = $rule($record->$path);
					}
					else { // remove
						$record->$path = Core\Data\Undefined::instance();
					}
				}
			}
			return Config\Properties\Helper::encode($record);
		}

	}

}