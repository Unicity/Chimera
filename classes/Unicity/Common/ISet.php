<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2012 Spadefoot Team
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

namespace Unicity\Common {

	use \Unicity\Common;

	/**
	 * This interface defines the contract for an immutable set.
	 *
	 * @access public
	 * @interface
	 * @package Common
	 */
	interface ISet extends Common\ICollection {

		/**
		 * This method determines whether the specified element is contained within the
		 * collection.
		 *
		 * @access public
		 * @param mixed $value                                      the element to be tested
		 * @return boolean                                          whether the specified element is contained
		 *                                                          within the collection
		 */
		public function hasValue($value);

		/**
		 * This method determines whether all elements in the specified array are contained
		 * within the collection.
		 *
		 * @access public
		 * @param \Traversable $values                              the collection to be tested
		 * @return boolean                                          whether all elements are contained within
		 *                                                          the collection
		 */
		public function hasValues($values);

	}

}