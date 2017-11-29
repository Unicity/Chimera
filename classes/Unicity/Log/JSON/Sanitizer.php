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

namespace Unicity\Log\JSON {

	use \Peekmo\JsonPath;
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
					$this->filters[] = (object) [
						'path' => $key->path,
						'rule' => $rule,
					];
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$input = static::input($input);
			$store = new JsonPath\JsonStore(json_decode($input->getBytes()));
			foreach ($this->filters as $filter) {
				$rule = $filter->rule;
				if (is_callable($rule)) {
					$results = $store->get($filter->path);
					if ($elements =& $results) {
						foreach ($elements as &$element) {
							$element = $rule($element);
						}
					}
				}
				else {
					$store->remove($filter->path);
				}
			}
			return $store->toString();
		}

	}

}