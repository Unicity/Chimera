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

namespace Unicity\VS\Validation {

	use \Unicity\Core;

	class Rule extends Core\Object {

		#region Flags -> Fixes

		/**
		 * This constant indicates that a field is to be removed from the data entirely.
		 *
		 * @access public
		 * @const string
		 */
		const REMOVE = 'Remove';

		/**
		 * This constant indicates that a field should be set with a value (e.g. a value
		 * is derivable from other content, is to be added for enrichment purposes, has
		 * been filtered, or is to be replaced).
		 *
		 * @access public
		 * @const string
		 */
		const SET = 'Set';

		#endregion

		#region Flags -> Violations

		/**
		 * This constant indicates that two or more fields conflict with each other.
		 *
		 * @access public
		 * @const string
		 */
		const CONFLICT = 'Conflict';

		/**
		 * This constant indicates that the data cannot be parsed or is incorrectly typed.
		 *
		 * @access public
		 * @const string
		 */
		const MALFORMED = 'Malformed';

		/**
		 * This constant indicates that a field does not match a particular pattern.
		 *
		 * @access public
		 * @const string
		 */
		const MISMATCH = 'Mismatch';

		/**
		 * This constant indicates that a field's value is missing.
		 *
		 * @access public
		 * @const string
		 */
		const MISSING = 'Missing';

		#endregion

	}

}