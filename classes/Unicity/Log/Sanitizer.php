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

namespace Unicity\Log {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;

	/**
	 * This class defines the contract for sanitizing messages.
	 *
	 * @access public
	 * @class
	 * @package Log
	 */
	abstract class Sanitizer extends Core\Object {

		protected static $rules = array(
			'mask' => '\\Unicity\\Log\\Masks::all',
			'mask_cc' => '\\Unicity\\Log\\Masks::creditCard',
			'mask_ip' => '\\Unicity\\Log\\Masks::ipAddress',
			'remove' => null,
		);

		public abstract function sanitize($input, array $metadata = array()) : string;

		protected static function filters($filters) : Common\IList {
			if ($filters instanceof IO\File) {
				return Common\Collection::useCollections(Config\JSON\Reader::load($filters)->read());
			}
			else if (is_string($filters)) {
				return Common\Collection::useCollections(json_decode($filters));
			}
			else {
				return Common\Collection::useCollections($filters);
			}
		}

		protected static function input($input) {
			if ($input instanceof IO\File) {
				return $input;
			}
			if (Common\StringRef::isTypeOf($input)) {
				return new IO\StringRef($input);
			}
			else {
				return new IO\StringRef((new Config\JSON\Writer($input))->render());
			}
		}

	}

}