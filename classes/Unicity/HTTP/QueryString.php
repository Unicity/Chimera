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

namespace Unicity\HTTP {

	use Unicity\Common;
	use \Unicity\Core;

	class QueryString extends Core\Object {

		public static function build($parameters, bool $prefix = true) : string {
			$parameters = Common\Collection::useArrays($parameters);
			if (is_array($parameters)) {
				$query_string = http_build_query($parameters);
				if (!empty($query_string) && $prefix) {
					$query_string = '?' . $query_string;
				}
			}
			return $query_string;
		}

	}

}