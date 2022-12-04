<?php

declare(strict_types = 1);

namespace Unicity\Core {

	class Env {

		public static function get(string $key, string $default = '') : string {
			$value = getenv($key);
			if (is_string($value)) {
				return trim($value);
			}
			return trim($default);
		}

	}

}