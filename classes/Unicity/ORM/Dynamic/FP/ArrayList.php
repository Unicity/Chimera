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

namespace Unicity\ORM\Dynamic\FP {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	/**
	 * This class provides a set of method to process an array list.
	 *
	 * @access public
	 * @class
	 * @package ORM
	 */
	class ArrayList {

		/**
		 * This method returns a list of those items that satisfy the predicate.
		 *
		 * @access public
		 * @static
		 * @param ORM\JSON\Model\ArrayList $xs                      the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return ORM\JSON\Model\ArrayList                         the list
		 */
		public static function filter(ORM\JSON\Model\ArrayList $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			foreach ($xs as $i => $x) {
				if ($predicate($x, $i)) {
					$ys->addValue($x);
				}
			}
			return $ys;
		}

		/**
		 * This method applies each item in this list to the subroutine function.
		 *
		 * @access public
		 * @static
		 * @param ORM\JSON\Model\ArrayList $xs                      the left operand
		 * @param callable $subroutine                              the subroutine function to be used
		 * @return ORM\JSON\Model\ArrayList                         the list
		 */
		public static function map(ORM\JSON\Model\ArrayList $xs, callable $subroutine) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			foreach ($xs as $i => $x) {
				$ys->addValue($subroutine($x, $i));
			}
			return $ys;
		}

	}

}