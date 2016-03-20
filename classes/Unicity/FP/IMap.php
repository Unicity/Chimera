<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2014-2016 Blue Snowman
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

namespace Unicity\FP {

	use \Unicity\Common;
	use \Unicity\FP;

	/**
	 * This class provides a set of method to process a map.
	 *
	 * @access public
	 * @class
	 * @package FP
	 */
	class IMap {

		/**
		 * This method (aka "every" or "forall") iterates over the items in the list, yielding each
		 * item to the predicate function, or fails the truthy test.  Opposite of "none".
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param callable $predicate                               the predicate function to be used
		 * @return boolean                                          whether each item passed the
		 *                                                          truthy test
		 */
		public static function all(Common\Mutable\IMap $xs, callable $predicate) {
			$i = 0;
			foreach ($xs as $k => $v) {
				$e = Common\Tuple::box2($k, $v);
				if (!$predicate($e, $i)) {
					return false;
				}
				$i++;
			}
			return true; // yes, empty returns "true"
		}

		/**
		 * This method (aka "exists" or "some") returns whether some of the items in the list passed the truthy
		 * test.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param callable $predicate                               the predicate function to be used
		 * @return boolean                                          whether some of the items passed
		 *                                                          the truthy test
		 */
		public static function any(Common\Mutable\IMap $xs, callable $predicate) {
			$i = 0;
			foreach ($xs as $k => $v) {
				$e = Common\Tuple::box2($k, $v);
				if ($predicate($e, $i)) {
					return true;
				}
				$i++;
			}
			return false;
		}

		/**
		 * This method removes all entries from the map.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return Common\Mutable\IMap                              the map
		 */
		public static function clear(Common\Mutable\IMap $xs) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			return $ys;
		}

		/**
		 * This method returns all key/value pairs in the map.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return Common\Mutable\IList                             all key/value pairs in the
		 *                                                          collection
		 */
		public static function entries(Common\Mutable\IMap $xs) {
			$ys = new Common\Mutable\ArrayList();
			foreach ($xs as $k => $v) {
				$ys->addValue(Common\Tuple::box2($k, $v));
			}
			return $ys;
		}

		/**
		 * This method returns a hash set of those items that satisfy the predicate.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IMap                              the map
		 */
		public static function filter(Common\Mutable\IMap $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$i = 0;
			foreach ($xs as $k => $v) {
				$e = Common\Tuple::box2($k, $v);
				if ($predicate($e, $i)) {
					$ys->putEntry($k, $v);
				}
				$i++;
			}
			return $ys;
		}

		/**
		 * This method applies a fold reduction on the list using the operator function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param callable $operator                                the operator function to be used
		 * @param mixed $initial                                    the initial value to be used
		 * @return mixed                                            the result
		 */
		public static function fold(Common\Mutable\IMap $xs, callable $operator, $initial) {
			$c = $initial;
			foreach ($xs as $k => $v) {
				$c = $operator($c, Common\Tuple::box2($k, $v));
			}
			return $c;
		}

		/**
		 * This method returns whether the specified key exists.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param mixed $k                                          the key to be found
		 * @return boolean                                          whether the key exists
		 */
		public static function hasKey(Common\Mutable\IMap $xs, $k) {
			return $xs->hasKey($k);
		}

		/**
		 * This method (aka "null") returns whether this list is empty.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return boolean                                          whether the list is empty
		 */
		public static function isEmpty(Common\Mutable\IMap $xs) {
			return $xs->isEmpty();
		}

		/**
		 * This method returns the item associated with the specified key.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param mixed $k                                          the key to be fetched
		 * @return mixed                                            the item associated with the
		 *                                                          specified key
		 */
		public static function item(Common\Mutable\IMap $xs, $k) {
			return $xs->getValue($k);
		}

		/**
		 * This method returns all of the items in the map.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return Common\Mutable\IList                             all items in the map
		 */
		public static function items(Common\Mutable\IMap $xs) {
			$ys = new Common\Mutable\ArrayList();
			$ys->addValues($xs->getValues());
			return $ys;
		}

		/**
		 * This method returns all of the keys in the map.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return Common\Mutable\IList                             all keys in the map
		 */
		public static function keys(Common\Mutable\IMap $xs) {
			$ys = new Common\Mutable\ArrayList();
			$ys->addValues($xs->getKeys());
			return $ys;
		}

		/**
		 * This method applies each item in this hash set to the subroutine function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param callable $subroutine                              the subroutine function to be used
		 * @return Common\Mutable\IMap                              the map
		 */
		public static function map(Common\Mutable\IMap $xs, callable $subroutine) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$i = 0;
			foreach ($xs as $k => $v) {
				$e = $subroutine(Common\Tuple::box2($k, $v), $i);
				$ys->putEntry($e->first(), $e->second());
				$i++;
			}
			return $ys;
		}

		/**
		 * This method returns a pair of maps: those items that satisfy the predicate and
		 * those items that do not satisfy the predicate.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map to be partitioned
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Tuple                                     the results
		 */
		public static function partition(Common\Mutable\IMap $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$passed = $class->newInstanceArgs($xs->__constructor_args());
			$failed = $class->newInstanceArgs($xs->__constructor_args());
			$i = 0;
			foreach ($xs as $k => $v) {
				$e = Common\Tuple::box2($k, $v);
				if ($predicate($e, $i)) {
					$passed->putEntry($e->first(), $e->second());
				}
				else {
					$failed->putEntry($e->first(), $e->second());
				}
			}
			return Common\Tuple::box2($passed, $failed);
		}

		/**
		 * This method adds the item with the specified key to the map (if it doesn't already
		 * exist).
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param Common\Tuple $e                                   the key/value pair to be put
		 *                                                          in the map
		 * @return Common\Mutable\IMap                              the map
		 */
		public static function putEntry(Common\Mutable\IMap $xs, Common\Tuple $e) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$ys->putEntries($xs);
			$ys->putEntry($e->first(), $e->second());
			return $ys;
		}

		/**
		 * This method returns an item after removing it from the map.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @param mixed $k                                          the key associated with the
		 *                                                          item to be removed
		 * @return Common\Mutable\IMap                              the item removed
		 */
		public static function removeKey(Common\Mutable\IMap $xs, $k) {
			return FP\IMap::filter($xs, function(Common\Tuple $e) use($k) {
				return ($e->first() != $k);
			});
		}

		/**
		 * This method returns the size of this collection.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IMap $xs                           the map
		 * @return integer                                          the size of this collection
		 */
		public static function size(Common\Mutable\IMap $xs) {
			return $xs->count();
		}

	}

}