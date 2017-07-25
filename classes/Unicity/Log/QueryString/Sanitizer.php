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

namespace Unicity\Log\QueryString {

	use \Peekmo\JsonPath;
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

		public function __construct(IO\File $file) {
			$config = Common\Collection::useCollections(Config\JSON\Reader::load($file)->read());
			$this->filters = array();
			foreach ($config->filters as $filter) {
				$delegate = $filter->hasKey('delegate') ? $filter->delegate : null;
				foreach ($filter->rules as $rule) {
					$this->filters[] = (object) [
						'delegate' => $delegate,
						'query' => Core\Convert::toString($rule->query),
					];
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$buffer = array();
			parse_str(ltrim($input->getBytes(), '?'), $buffer);
			$store = new JsonPath\JsonStore($buffer);
			unset($buffer);
			foreach ($this->filters as $filter) {
				$delegate = $filter->delegate;
				if (is_callable($delegate)) {
					$results = $store->get($filter->query);
					if ($elements =& $results) {
						foreach ($elements as &$element) {
							$element = $delegate($element);
						}
					}
				}
				else {
					$store->remove($filter->query);
				}
			}
			$query = http_build_query($store->toArray());
			if (!empty($query)) {
				$query = '?' . $query;
			}
			return new IO\StringRef($query);
		}

	}

}