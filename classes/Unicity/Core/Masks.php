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

namespace Unicity\Core {

	use \Unicity\Core;

	/**
	 * This class provides a set of helper methods for masking value.
	 *
	 * @access public
	 * @class
	 * @package Log
	 */
	class Masks extends Core\AbstractObject {

		public static function all($value, string $symbol = 'x') {
			if ($value !== null) {
				return str_repeat($symbol, strlen(Core\Convert::toString($value)));
			}
			return $value;
		}

		public static function accountNumber($value, string $symbol = 'x', int $last = 4) {
			if ($value !== null) {
				$value = strtolower(str_replace('*', 'x', Core\Convert::toString($value)));
				if ($symbol !== 'x') {
					$value = str_replace('x', $symbol, $value);
				}
				$length = strlen($value);
				if ($length > $last) {
					return str_repeat($symbol, $length - $last) . substr($value, $last * -1, $last);
				}
			}
			return $value;
		}

		public static function creditCard($value, string $symbol = 'x', bool $first6 = true) {
			if ($value !== null) {
				$value = strtolower(str_replace('*', 'x', Core\Convert::toString($value)));
				$value = preg_replace('/[^x0-9]/', '', $value);
				if ($symbol !== 'x') {
					$value = str_replace('x', $symbol, $value);
				}
				$length = strlen($value);
				if ($length > 10) {
					if ($first6) {
						return substr($value, 0, 6) . str_repeat($symbol, $length - 10) . substr($value, -4, 4);
					}
					return str_repeat($symbol, $length - 4) . substr($value, -4, 4);
				}
			}
			return $value;
		}

		public static function ipAddress($value, string $symbol = 'x') {
			if ($value !== null) {
				$segments = explode('.', Core\Convert::toString($value));
				$segments = array_map(function($segment) use($symbol) {
					return str_repeat($symbol, strlen($segment));
				}, $segments);
				return implode('.', $segments);
			}
			return $value;
		}

		public static function last($value, string $symbol = 'x', int $count = 4) {
			$count = abs($count);
			if (($value !== null) && ($count > 0)) {
				$value = Core\Convert::toString($value);
				$length = strlen($value);
				if ($length <= $count) {
					return str_repeat($symbol, $length);
				}
				return substr($value, 0, $count * -1) . str_repeat($symbol, $count);
			}
			return $value;
		}

		public static function token($value, string $symbol = 'x', bool $first6 = true) {
			if ($value !== null) {
				$value = preg_replace('/[^A-Z0-9]/i', '', Core\Convert::toString($value));
				$length = strlen($value);
				if ($length > 10) {
					if ($first6) {
						return substr($value, 0, 6) . str_repeat($symbol, $length - 10) . substr($value, -4, 4);
					}
					return str_repeat($symbol, $length - 4) . substr($value, -4, 4);
				}
			}
			return $value;
		}

	}

}
