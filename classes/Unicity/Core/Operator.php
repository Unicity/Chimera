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

namespace Unicity\Core {

	use \Unicity\Core;

	/**
	 * This class converts a base data type to another base data type.
	 *
	 * @access public
	 * @class
	 * @package Core
	 */
	class Operator extends Core\Object {

		public static function isEquatable($x, $op, $y) {
			switch (strtolower($op)) {
				case '<':
				case 'lt':
					return ($x < $y);
				case '≤':
				case '<=':
				case 'le':
					return ($x <= $y);
				case '=':
				case '==':
				case 'eq':
					return ($x == $y);
				case '===':
					return ($x === $y);
				case '≠':
				case '!=':
				case '<>':
				case 'ne':
					return ($x != $y);
				case '!==':
					return ($x !== $y);
				case '≥':
				case '>=':
				case 'ge':
					return ($x >= $y);
				case '>':
				case 'gt':
					return ($x > $y);
			}
			return false;
		}

	}

}