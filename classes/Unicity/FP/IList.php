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
	 * This class provides a set of method to process a list.
	 *
	 * @access public
	 * @class
	 * @package FP
	 */
	class IList {

		/**
		 * This method (aka "every" and "forall") iterates over the items in the list, yielding
		 * each item to the predicate function, or fails the truthy test.  Opposite of "none".
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return boolean                                          whether each item passed the
		 *                                                          truthy test
		 */
		public static function all(Common\Mutable\IList $xs, callable $predicate) {
			foreach ($xs as $i => $x) {
				if (!$predicate($x, $i)) {
					return false;
				}
			}
			return true; // yes, empty returns "true"
		}

		/**
		 * This method (aka "exists" and "some") returns whether some of the items in the list
		 * passed the truthy test.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return boolean                                          whether some of the items passed
		 *                                                          the truthy test
		 */
		public static function any(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::indexWhere($xs, $predicate) > -1;
		}

		/**
		 * This method appends the specified object to this object's list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to be appended
		 * @return Common\Mutable\IList                             the list
		 */
		public static function append(Common\Mutable\IList $xs, $y) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$ys->addValues($xs->toArray());
			$ys->addValue($y);
			return $ys;
		}

		/**
		 * This method appends all objects in the specified list to this object's list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param Common\Mutable\IList $ys                          the list to be appended
		 * @return Common\Mutable\IList                             the list
		 */
		public static function appendAll(Common\Mutable\IList $xs, Common\Mutable\IList $ys) {
			$class = new \ReflectionClass(get_class($xs));
			$zs = $class->newInstanceArgs($xs->__constructor_args());
			$zs->addValues($xs);
			$zs->addValues($ys);
			return $zs;
		}

		/**
		 * This method returns a tuple where the first item contains longest prefix of the array
		 * list that does not satisfy the predicate and the second item contains the remainder.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Tuple                                     the tuple
		 */
		public static function break_(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::span($xs, function($x, $i) use ($predicate) {
				return !$predicate($x, $i);
			});
		}

		/**
		 * This method evaluates whether the specified object is contained within the list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to find
		 * @return boolean                                          whether the specified object is
		 *                                                          contained within the list
		 */
		public static function contains(Common\Mutable\IList $xs, $y) {
			return $xs->hasValue($y);
		}

		/**
		 * This method remove the first occurrence that equals the specified object.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to be removed
		 * @return Common\Mutable\IList                             the list
		 */
		public static function delete(Common\Mutable\IList $xs, $y) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$skip = false;
			foreach ($xs as $x) {
				if (($x == $y) && !$skip) {
					$skip = true;
					continue;
				}
				$ys->addValue($x);
			}
			return $ys;
		}

		/**
		 * This method returns the list after dropping the first "n" items.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $n                                        the number of items to drop
		 * @return Common\Mutable\IList                             the list
		 */
		public static function drop(Common\Mutable\IList $xs, $n) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$length = $xs->count();
			for ($i = $n; $i < $length; $i++) {
				$ys->addValue($xs->getValue($i));
			}
			return $ys;
		}

		/**
		 * This method returns the list from item where the predicate function fails.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function dropWhile(Common\Mutable\IList $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$failed = false;
			foreach ($xs as $i => $x) {
				if (!$predicate($x, $i) || $failed) {
					$ys->addValue($x);
					$failed = true;
				}
			}
			return $ys;
		}

		/**
		 * This method returns the list from item where the predicate function doesn't fail.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function dropWhileEnd(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::dropWhile($xs, function($x, $i) use ($predicate) {
				return !$predicate($x, $i);
			});
		}

		/**
		 * This method iterates over the items in the list, yielding each item to the procedure
		 * function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $procedure                               the procedure function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function each(Common\Mutable\IList $xs, callable $procedure) {
			foreach ($xs as $i => $x) {
				$procedure($x, $i);
			}
			return $xs;
		}

		/**
		 * This method returns a list of those items that satisfy the predicate.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function filter(Common\Mutable\IList $xs, callable $predicate) {
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
		 * This method returns the list flattened.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return Common\Mutable\IList                             the flattened list
		 */
		public static function flatten(Common\Mutable\IList $xs) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			foreach ($xs as $i => $x) {
				if ($x instanceof Common\Mutable\IList) {
					$ys->addValues(FP\IList::flatten($x));
				}
				else {
					$ys->addValue($x);
				}
			}
			return $ys;
		}

		/**
		 * This method applies a left-fold reduction on the list using the operator function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $operator                                the operator function to be used
		 * @param mixed $initial                                    the initial value to be used
		 * @return mixed                                            the result
		 */
		public static function foldLeft(Common\Mutable\IList $xs, callable $operator, $initial) {
			$c = $initial;
			foreach ($xs as $i => $x) {
				$c = $operator($c, $x);
			}
			return $c;
		}

		/**
		 * This method applies a right-fold reduction on the list using the operation function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $operator                                the operator function to be used
		 * @param mixed $initial                                    the initial value to be used
		 * @return mixed                                            the result
		 */
		public static function foldRight(Common\Mutable\IList $xs, callable $operator, $initial) {
			$c = $initial;
			for ($i = $xs->count() - 1; $i >= 0; $i--) {
				$c = $operator($c, $xs->getValue($i));
			}
			return $c;
		}

		/**
		 * This method returns a hash map of lists of items that are considered in the same group.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $subroutine                              the subroutine to be used
		 * @return Common\Mutable\IMap                              a hash map of lists of items that
		 *                                                          are considered in the same group
		 */
		public static function group(Common\Mutable\IList $xs, callable $subroutine) {
			$groups = new Common\Mutable\HashMap();
			foreach ($xs as $i => $x) {
				$k = $subroutine($x, $i);
				$ys = ($groups->hasKey($k))
					? $groups->getValue($k)
					: new Common\Mutable\ArrayList();
				$ys->addValue($x);
				$groups->putEntry($k, $ys);
			}
			return $groups;
		}

		/**
		 * This method returns the head object in this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return mixed                                            the head object in this list
		 */
		public static function head(Common\Mutable\IList $xs) {
			return $xs->getValue(0);
		}

		/**
		 * This method returns the index of the first occurrence of the object; otherwise, it returns -1;
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to be searched for
		 * @return integer                                          the index of the first occurrence
		 *                                                          or otherwise -1
		 */
		public static function indexOf(Common\Mutable\IList $xs, $y) {
			return FP\IList::indexWhere($xs, function($x) use ($y) {
				return ($x == $y);
			});
		}

		/**
		 * This method returns the index of the first occurrence that satisfies the predicate; otherwise,
		 * it returns -1;
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return integer                                          the index of the first occurrence
		 *                                                          or otherwise -1
		 */
		public static function indexWhere(Common\Mutable\IList $xs, callable $predicate) {
			foreach ($xs as $i => $x) {
				if ($predicate($x, $i)) {
					return $i;
				}
			}
			return -1;
		}

		/**
		 * This method returns all but the last item of in the list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return Common\Mutable\IList                             the list, minus the last item
		 */
		public static function init(Common\Mutable\IList $xs) {
			return FP\IList::take($xs, $xs->count() - 1);
		}

		/**
		 * The method intersperses the specified object between each item in the list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to be interspersed
		 * @return Common\Mutable\IList                             the list
		 */
		public static function intersperse(Common\Mutable\IList $xs, $y) {
			$ys = new Common\Mutable\ArrayList();
			$length = $xs->count();
			if ($length > 0) {
				$ys->addValue($xs->getValue(0));
				for ($i = 1; $i < $length; $i++) {
					$ys->addValue($y);
					$ys->addValue($xs->getValue($i));
				}
			}
			return $ys;
		}

		/**
		 * This method (aka "null") returns whether this list is empty.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return boolean                                          whether the list is empty
		 */
		public static function isEmpty(Common\Mutable\IList $xs) {
			return $xs->isEmpty();
		}

		/**
		 * This method returns the item at the specified index.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $i                                        the index of the item
		 * @return mixed                                            the item at the specified index
		 */
		public static function item(Common\Mutable\IList $xs, $i) {
			return $xs->getValue($i);
		}

		/**
		 * This method returns the last item in this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return mixed                                            the last item in this linked
		 *                                                          list
		 */
		public static function last(Common\Mutable\IList $xs) {
			return $xs->getValue($xs->count() - 1);
		}

		/**
		 * This method returns the length of this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return integer                                          the length of this list
		 */
		public static function length(Common\Mutable\IList $xs) {
			return $xs->count();
		}

		/**
		 * This method applies each item in this list to the subroutine function.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $subroutine                              the subroutine function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function map(Common\Mutable\IList $xs, callable $subroutine) {
			$ys = new Common\Mutable\ArrayList();
			foreach ($xs as $i => $x) {
				$ys->addValue($subroutine($x, $i));
			}
			return $ys;
		}

		/**
		 * This method iterates over the items in the list, yielding each item to the
		 * predicate function, or fails the falsy test.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return boolean                                          whether each item passed the
		 *                                                          falsy test
		 */
		public static function none(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::all($xs, function($x, $i) use ($predicate) {
				return !$predicate($x, $i);
			});
		}

		/**
		 * This method returns a list containing only unique items from the specified
		 * list (i.e. duplicates are removed).
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the list to be processed
		 * @return Common\Mutable\IList                             a list with the duplicates
		 *                                                          removed
		 */
		public static function nub(Common\Mutable\IList $xs) {
			$zs = new Common\Mutable\HashSet();
			return FP\IList::filter($xs, function($x) use ($zs) {
				if ($zs->hasValue($x)) {
					return false;
				}
				$zs->putValue($x);
				return true;
			});
		}

		/**
		 * This method returns a pair of lists: those items that satisfy the predicate and
		 * those items that do not satisfy the predicate.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the list to be partitioned
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Tuple                                     the results
		 */
		public static function partition(Common\Mutable\IList $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$passed = $class->newInstanceArgs($xs->__constructor_args());
			$failed = $class->newInstanceArgs($xs->__constructor_args());
			foreach ($xs as $i => $x) {
				if ($predicate($x, $i)) {
					$passed->addValue($x);
				}
				else {
					$failed->addValue($x);
				}
			}
			return Common\Tuple::box2($passed, $failed);
		}

		/**
		 * This method returns a list of values matching the specified key.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xss                         the list to be processed
		 * @param mixed $k                                          the key associated with value to be
		 *                                                          plucked
		 * @return Common\Mutable\IList                             a list of values matching the specified
		 *                                                          key
		 */
		public static function pluck(Common\Mutable\IList $xss, $k) {
			return FP\IList::foldLeft($xss, function(Common\Mutable\IList $ys, Common\Mutable\IMap $xs) use ($k) {
				if ($xs->hasKey($k)) {
					$ys->addValue($xs->getValue($k));
				}
				return $ys;
			}, new Common\Mutable\ArrayList());
		}

		/**
		 * This method prepends the specified object to the front of this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param mixed $y                                          the object to be prepended
		 * @return Common\Mutable\IList                             the list
		 */
		public static function prepend(Common\Mutable\IList $xs, $y) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$ys->addValue($y);
			$ys->addValues($xs);
			return $ys;
		}

		/**
		 * This method returns the list within the specified range.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $start                                    the starting index
		 * @param integer $end                                      the ending index
		 * @return Common\Mutable\IList                             the list
		 */
		public static function range(Common\Mutable\IList $xs, $start, $end) {
			return FP\IList::drop(FP\IList::take($xs, $end), $start);
		}

		/**
		 * This method (aka "remove") returns a list containing those items that do not
		 * satisfy the predicate.  Opposite of "filter".
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             a list containing those items that
		 *                                                          do not satisfy the predicate
		 */
		public static function reject(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::filter($xs, function($x, $i) use ($predicate) {
				return !$predicate($x, $i);
			});
		}

		/**
		 * This method reverses the order of the items in this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return Common\Mutable\IList                             the list
		 */
		public static function reverse(Common\Mutable\IList $xs) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$ys->addValues(array_reverse($xs->toArray()));
			return $ys;
		}

		/**
		 * This method shuffles the items in the list using the Fisher-Yates shuffle.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the list to be shuffled
		 * @return Common\Mutable\IList                             the shuffled list
		 *
		 * @see http://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
		 */
		public static function shuffle(Common\Mutable\IList $xs) {
			$class = new \ReflectionClass(get_class($xs));
			$zs = $class->newInstanceArgs($xs->__constructor_args());
			$ys = $xs->toArray();
			shuffle($ys);
			$zs->addValues($ys);
			return $zs;
		}

		/**
		 * This method returns the extracted slice of the list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $offset                                   the starting index
		 * @param integer $length                                   the length of the slice
		 * @return Common\Mutable\IList                             the list
		 */
		public static function slice(Common\Mutable\IList $xs, $offset, $length) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			$ys->addValues(array_slice($xs->toArray(), $offset, $length));
			return $ys;
		}

		/**
		 * This method returns a tuple where the first item contains longest prefix of the array
		 * list that satisfies the predicate and the second item contains the remainder.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Tuple                                     the tuple
		 */
		public static function span(Common\Mutable\IList $xs, callable $predicate) {
			return Common\Tuple::box2(
				FP\IList::takeWhile($xs, $predicate),
				FP\IList::dropWhile($xs, $predicate)
			);
		}

		/**
		 * This method returns a tuple where the first item contains the first "n" items
		 * in the list and the second item contains the remainder.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $n                                        the number of items to take
		 * @return Common\Tuple                                     the tuple
		 */
		public static function split(Common\Mutable\IList $xs, $n) {
			return Common\Tuple::box2(
				FP\IList::take($xs, $n),
				FP\IList::drop($xs, $n)
			);
		}

		/**
		 * This method returns the tail of this list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @return Common\Mutable\IList                             the tail of this list
		 */
		public static function tail(Common\Mutable\IList $xs) {
			return FP\IList::drop($xs, 1);
		}

		/**
		 * This method returns the first "n" items in the list.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param integer $n                                        the number of items to take
		 * @return Common\Mutable\IList                             the list
		 */
		public static function take(Common\Mutable\IList $xs, $n) {
			return FP\IList::takeWhile($xs, function($x, $i) use ($n) {
				return ($i < $n);
			});
		}

		/**
		 * This method returns each item in this list until the predicate fails.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function takeWhile(Common\Mutable\IList $xs, callable $predicate) {
			$class = new \ReflectionClass(get_class($xs));
			$ys = $class->newInstanceArgs($xs->__constructor_args());
			foreach ($xs as $i => $x) {
				if (!$predicate($x, $i)) {
					break;
				}
				$ys->addValue($x);
			}
			return $ys;
		}

		/**
		 * This method returns each item in this list until the predicate doesn't fail.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param callable $predicate                               the predicate function to be used
		 * @return Common\Mutable\IList                             the list
		 */
		public static function takeWhileEnd(Common\Mutable\IList $xs, callable $predicate) {
			return FP\IList::takeWhile($xs, function($x, $i) use ($predicate) {
				return !$predicate($x, $i);
			});
		}

		/**
		 * This method returns a tuple of two (or more) lists after splitting a list of tuple
		 * groupings.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xss                          a list of tuple groupings
		 * @return Common\Tuple                                      a tuple of two (or more) lists
		 */
		public static function unzip(Common\Mutable\IList $xss) {
			$ys = new Common\Mutable\ArrayList();
			$zs = new Common\Mutable\ArrayList();
			foreach ($xss as $i => $xs) {
				$ys->addValue($xs->first());
				$zs->addValue($xs->second());
			}
			return Common\Tuple::box2($ys, $zs);
		}

		/**
		 * This method returns a new list of tuple pairings.
		 *
		 * @access public
		 * @static
		 * @param Common\Mutable\IList $xs                          the left operand
		 * @param Common\Mutable\IList $ys                          the right operand
		 * @return Common\Mutable\IList                             a new list of tuple pairings
		 */
		public static function zip(Common\Mutable\IList $xs, Common\Mutable\IList $ys) {
			$zs = new Common\Mutable\ArrayList();
			$length = min($xs->count(), $ys->count());
			for ($i = 0; $i < $length; $i++) {
				$zs->addValue(Common\Tuple::box2($xs->getValue($i), $ys->getValue($i)));
			}
			return $zs;
		}

	}

}