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

namespace Unicity\FP;

use Unicity\Common;
use Unicity\FP;

/**
 * This class provides a set of method to process a set.
 *
 * @access public
 * @class
 * @package FP
 */
class ISet
{
    /**
     * This method (aka "every" or "forall") iterates over the items in the list, yielding each
     * item to the predicate function, or fails the truthy test.  Opposite of "none".
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $predicate the predicate function to be used
     * @return boolean whether each item passed the
     *                 truthy test
     */
    public static function all(Common\Mutable\ISet $xs, callable $predicate)
    {
        foreach ($xs as $i => $x) {
            if (!$predicate($x, $i)) {
                return false;
            }
        }

        return true; // yes, empty returns "true"
    }

    /**
     * This method (aka "exists" or "some") returns whether some of the items in the list passed the truthy
     * test.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $predicate the predicate function to be used
     * @return boolean whether some of the items
     *                 passed the truthy test
     */
    public static function any(Common\Mutable\ISet $xs, callable $predicate)
    {
        foreach ($xs as $i => $x) {
            if ($predicate($x, $i)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method removes all entries from the set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @return Common\Mutable\ISet the set
     */
    public static function clear(Common\Mutable\ISet $xs)
    {
        $class = new \ReflectionClass(get_class($xs));
        $ys = $class->newInstanceArgs($xs->__constructor_args());

        return $ys;
    }

    /**
     * This method returns a set which represents the symmetric difference between
     * the two specified sets.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the right operand
     * @return Common\Mutable\ISet a set which represents the symmetric
     *                             difference of the two specified sets
     */
    public static function difference(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        $as = FP\ISet::union($xs, $ys);
        $bs = FP\ISet::intersection($xs, $ys);
        $cs = FP\ISet::without($as, $bs);

        return $cs;
    }

    /**
     * This method returns a set of those items that satisfy the predicate.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $predicate the predicate function to be used
     * @return Common\Mutable\ISet the set
     */
    public static function filter(Common\Mutable\ISet $xs, callable $predicate)
    {
        $class = new \ReflectionClass(get_class($xs));
        $ys = $class->newInstanceArgs($xs->__constructor_args());
        foreach ($xs as $i => $x) {
            if ($predicate($x, $i)) {
                $ys->putItem($x);
            }
        }

        return $ys;
    }

    /**
     * This method applies a fold reduction on the list using the operator function.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $operator the operator function to be used
     * @param mixed $initial the initial value to be used
     * @return mixed the result
     */
    public static function fold(Common\Mutable\ISet $xs, callable $operator, $initial)
    {
        $c = $initial;

        foreach ($xs as $x) {
            $c = $operator($c, $x);
        }

        return $c;
    }

    /**
     * This method returns the item associated with the specified key.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param mixed $x the item to be found
     * @return boolean whether the item exists
     */
    public static function hasItem(Common\Mutable\ISet $xs, $x)
    {
        return $xs->hasValue($x);
    }

    /**
     * This method returns a set which represents the intersection between the two
     * specified sets.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the right operand
     * @return Common\Mutable\ISet a set which represents the intersection
     *                             of the two specified sets
     */
    public static function intersection(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        $zs = new Common\Mutable\HashSet();
        foreach ($ys as $y) {
            if ($xs->hasValue($y)) {
                $zs->putValue($zs);
            }
        }

        return $zs;
    }

    /**
     * This method (aka "null") returns whether this list is empty.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @return boolean whether the list is empty
     */
    public static function isEmpty(Common\Mutable\ISet $xs)
    {
        return $xs->isEmpty();
    }

    /**
     * This method returns whether the second set is a subset of the first set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the right operand
     * @return boolean whether the second set is a
     *                 subset of the first set
     */
    public static function isSubset(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        foreach ($ys as $y) {
            if (!$xs->hasValue($y)) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method returns whether the second set is a superset of the first set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the left operand
     * @return boolean whether the second set is a
     *                 superset of the first set
     */
    public static function isSuperset(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        foreach ($xs as $x) {
            if (!$ys->hasValue($x)) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method returns all of the items in the set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @return Common\Mutable\IList all items in the set
     */
    public static function items(Common\Mutable\ISet $xs)
    {
        $ys = new Common\Mutable\ArrayList();
        $ys->addValues($xs);

        return $ys;
    }

    /**
     * This method applies each item in this set to the subroutine function.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $subroutine the subroutine function to be used
     * @return Common\Mutable\ISet the set
     */
    public static function map(Common\Mutable\ISet $xs, callable $subroutine)
    {
        $zs = new Common\Mutable\HashSet();
        foreach ($xs as $i => $x) {
            $zs->putValue($subroutine($x, $i));
        }

        return $zs;
    }

    /**
     * This method returns a pair of sets: those items that satisfy the predicate and
     * those items that do not satisfy the predicate.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param callable $predicate the predicate function to be used
     * @return Common\Tuple the results
     */
    public static function partition(Common\Mutable\ISet $xs, callable $predicate)
    {
        $class = new \ReflectionClass(get_class($xs));
        $passed = $class->newInstanceArgs($xs->__constructor_args());
        $failed = $class->newInstanceArgs($xs->__constructor_args());
        foreach ($xs as $i => $x) {
            if ($predicate($x, $i)) {
                $passed->putItem($x);
            } else {
                $failed->putItem($x);
            }
        }

        return Common\Tuple::box2($passed, $failed);
    }

    /**
     * This method returns the power set of the specified set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @return Common\Mutable\ISet the power set
     *
     * @see http://rosettacode.org/wiki/Power_Set
     */
    public static function powerset(Common\Mutable\ISet $xs)
    {
        $css = new Common\Mutable\HashSet();
        $css->putValue(new Common\Mutable\HashSet());
        foreach ($xs as $x) {
            $as = new Common\Mutable\HashSet();
            foreach ($css as $cs) {
                $as->putValue($cs);
                $bs = new Common\Mutable\HashSet();
                $bs->putValues($cs);
                $bs->putValue($x);
                $as->putValue($bs);
            }
            $css = $as;
        }

        return $css;
    }

    /**
     * This method returns the cartesian product of the specified sets.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the sets to be evaluated
     * @param Common\Mutable\ISet[] ...$xss the sets to be evaluated
     * @return Common\Mutable\ISet the cartesian product
     */
    public static function product(Common\Mutable\ISet $xs, Common\Mutable\ISet ...$xss)
    {
        array_unshift($xss, $xs);

        return FP\ISet::_product($xss, 0);
    }

    /**
     * This method acts as a helper to finding the cartesian product of the specified
     * sets.
     *
     * @access private
     * @static
     * @param array $xss the sets to be evaluated
     * @param integer $i the index
     * @return Common\Mutable\ISet the cartesian product
     *
     * @see http://stackoverflow.com/questions/714108/cartesian-product-of-arbitrary-sets-in-java
     */
    private static function _product(array $xss, $i)
    {
        $ys = new Common\Mutable\HashSet();
        if ($i == count($xss)) {
            $ys->putValue(new Common\Mutable\HashSet());
        } else {
            foreach ($xss[$i] as $xs) {
                $zss = FP\ISet::_product($xss, $i + 1);
                foreach ($zss as $zs) {
                    $zs->putValue($xs);
                    $ys->putValue($zs);
                }
            }
        }

        return $ys;
    }

    /**
     * This method puts the item into the set (if it doesn't already exist).
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param mixed $x the item to be stored
     * @return Common\Mutable\ISet the set
     */
    public static function putItem(Common\Mutable\ISet $xs, $x)
    {
        $class = new \ReflectionClass(get_class($xs));
        $ys = $class->newInstanceArgs($xs->__constructor_args());
        $ys->putValues($xs);
        $ys->putValue($x);

        return $ys;
    }

    /**
     * This method returns the set with the item removed.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param mixed $x the item to be removed
     * @return Common\Mutable\ISet the set
     */
    public static function removeItem(Common\Mutable\ISet $xs, $x)
    {
        $class = new \ReflectionClass(get_class($xs));
        $ys = $class->newInstanceArgs($xs->__constructor_args());
        $ys->putValues($xs);
        $ys->removeValue($x);

        return $ys;
    }

    /**
     * This method returns the size / cardinality of the set.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @return integer the size / cardinality of the set
     */
    public static function size(Common\Mutable\ISet $xs)
    {
        return $xs->count();
    }

    /**
     * This method returns a set which represents the union of the two specified sets.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the right operand
     * @return Common\Mutable\ISet a set which represents the union
     *                             of the two specified sets
     */
    public static function union(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        $zs = new Common\Mutable\HashSet();
        $zs->putValues($xs);
        $zs->putValues($ys);

        return $zs;
    }

    /**
     * This method returns a set which represents the asymmetric difference between
     * the two specified sets.
     *
     * @access public
     * @static
     * @param Common\Mutable\ISet $xs the left operand
     * @param Common\Mutable\ISet $ys the right operand
     * @return Common\Mutable\ISet a set which represents the (asymmetric)
     *                             difference of the two specified sets
     */
    public static function without(Common\Mutable\ISet $xs, Common\Mutable\ISet $ys)
    {
        $class = new \ReflectionClass(get_class($xs));
        $zs = $class->newInstanceArgs($xs->__constructor_args());
        $zs->putValues($xs);
        $zs->removeValues($ys);

        return $zs;
    }

}
