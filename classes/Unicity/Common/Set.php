<?php

/**
 * Copyright 2015 Unicity International
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
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class provides a set of helper function for working with sets.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Common
	 *
	 * @see http://docs.oracle.com/javase/tutorial/collections/interfaces/set.html
	 */
	abstract class Set extends Core\Object {

		/**
		 * This method returns the cardinality of the specified hash set.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $set                                  the hash set to be evaluated
		 * @return integer                                          the cardinality of the specified
		 *                                                          hash set
		 */
		public static function cardinality(Common\ISet $set) {
			return $set->count();
		}

		/**
		 * This method returns the cartesian product of the specified hash sets.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $sets                                 the hash sets to be evaluated
		 * @return Common\ISet                                      the cartesian product of the specified
		 *                                                          hash sets
		 *
		 * @see http://stackoverflow.com/questions/714108/cartesian-product-of-arbitrary-sets-in-java
		 */
		public static function cartesianProduct(/*Common\ISet... sets*/) {
			if (func_num_args() < 2) {
				throw new Throwable\Runtime\Exception('Unable to perform evaluation. At least two sets must be passed.');
			}
			$sets = func_get_args();
			return static::_cartesianProduct(0, $sets);
		}

		/**
		 * This method acts as a helper to finding the cartesian product of the specified
		 * hash sets.
		 *
		 * @access protected
		 * @static
		 * @param integer $index                                    the index
		 * @param Common\ISet $sets                                 the hash sets to be evaluated
		 * @return Common\ISet                                      the cartesian product of the specified
		 *                                                          hash sets
		 *
		 * @see http://stackoverflow.com/questions/714108/cartesian-product-of-arbitrary-sets-in-java
		 */
		protected static function _cartesianProduct($index, $sets) {
			$hashset = new Common\Mutable\HashSet();
			if ($index == count($sets)) {
				$hashset->putValue(new Common\Mutable\HashSet());
			}
			else {
				foreach ($sets[$index] as $object) {
					$cartesian_product = static::_cartesianProduct($index + 1, $sets);
					foreach ($cartesian_product as $set) {
						$set->putValue($object);
						$hashset->putValue($set);
					}
				}
			}
			return $hashset;
		}

		/**
		 * This method returns a hash set which represents the (asymmetric) difference between
		 * the two specified sets.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      a hash set which represents the (asymmetric)
		 *                                                          difference of the two specified sets
		 */
		public static function difference(Common\ISet $s1, Common\ISet $s2) {
			$s0 = new Common\Mutable\HashSet($s1);
			$s0->removeValues($s2);
			return $s0;
		}

		/**
		 * This method returns a hash set which represents the intersection between the two
		 * specified sets.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      a hash set which represents the intersection
		 *                                                          of the two specified sets
		 */
		public static function intersection(Common\ISet $s1, Common\ISet $s2) {
			$s0 = new Common\Mutable\HashSet($s1);
			$s0->retainValues($s2);
			return $s0;
		}

		/**
		 * This method returns whether the second hash set is a subset of the first hash
		 * set.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      whether the second hash set is a
		 *                                                          subset of the first hash set
		 */
		public static function isSubset(Common\ISet $s1, Common\ISet $s2) {
			return $s1->hasValues($s2);
		}

		/**
		 * This method returns whether the second hash set is a superset of the first hash
		 * set.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      whether the second hash set is a
		 *                                                          superset of the first hash set
		 */
		public static function isSuperset(Common\ISet $s1, Common\ISet $s2) {
			return $s2->hasValues($s1);
		}

		/**
		 * This method returns the power set of the specified set.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $set                                  the hash set to be used
		 * @return Common\ISet                                      the power set
		 *
		 * @see http://rosettacode.org/wiki/Power_Set
		 */
		public static function powerset(Common\ISet $set) {
			$powerset = new Common\Mutable\HashSet();
			$powerset->putValue(new Common\Mutable\HashSet());
			foreach ($set as $element) {
				$hashset = new Common\Mutable\HashSet();
				foreach ($powerset as $subset) {
					$hashset->putValue($subset);
					$temp = new Common\Mutable\HashSet($subset);
					$temp->putValue($element);
					$hashset->putValue($temp);
				}
				$powerset = $hashset;
			}
			return $powerset;
		}

		/**
		 * This method returns a hash set which represents the symmetric difference between
		 * the two specified sets.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      a hash set which represents the symmetric
		 *                                                          difference of the two specified sets
		 */
		public static function symmetricDifference(Common\ISet $s1, Common\ISet $s2) {
			$s0 = new Common\Mutable\HashSet($s1);
			$s0->putValues($s2);
			$tmp = new Common\Mutable\HashSet($s1);
			$tmp->retainValues($s2);
			$s0->removeValues($tmp);
			return $s0;
		}

		/**
		 * This method returns a hash set which represents the union of the two specified
		 * sets.
		 *
		 * @access public
		 * @static
		 * @param Common\ISet $s1                                   the first set
		 * @param Common\ISet $s2                                   the second set
		 * @return Common\ISet                                      a hash set which represents the union
		 *                                                          of the two specified sets
		 */
		public static function union(Common\ISet $s1, Common\ISet $s2) {
			$s0 = new Common\Mutable\HashSet($s1);
			$s0->putValues($s2);
			return $s0;
		}

	}

}
