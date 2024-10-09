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

declare(strict_types=1);

namespace Unicity\AOP;

use Unicity\AOP;

/**
 * This interface is used to mark a class as an aspect in Aspect Oriented Programming (AOP).  An aspect
 * is a class containing a set of advice methods.
 *
 * @access public
 * @interface
 * @package AOP
 */
interface IAspect
{
    /**
     * This method runs before the concern's execution.
     *
     * @access public
     * @param AOP\JoinPoint $joinPoint the join point being used
     */
    //public function before(AOP\JoinPoint $joinPoint) : void;

    /**
     * This method runs around (i.e before and after) the other advice types and the concern's
     * execution.
     *
     * @access public
     * @param AOP\JoinPoint $joinPoint the join point being used
     */
    //public function around(AOP\JoinPoint $joinPoint) : void;

    /**
     * This method runs when the concern's execution is successful (and a result is returned).
     *
     * @access public
     * @param AOP\JoinPoint $joinPoint the join point being used
     */
    //public function afterReturning(AOP\JoinPoint $joinPoint) : void;

    /**
     * This method runs when the concern's throws an exception.
     *
     * @access public
     * @param AOP\JoinPoint $joinPoint the join point being used
     */
    //public function afterThrowing(AOP\JoinPoint $joinPoint) : void;

    /**
     * This method runs when the concern's execution is finished (even if an exception was thrown).
     *
     * @access public
     * @param AOP\JoinPoint $joinPoint the join point being used
     */
    //public function after(AOP\JoinPoint $joinPoint) : void;

}
