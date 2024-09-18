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

namespace Unicity\Throwable\Overflow;

use Unicity\Core;
use Unicity\Throwable;

/**
 * This class represents an Overflow Exception.
 *
 * @access public
 * @class
 * @package Throwable
 */
class Exception extends \OverflowException implements Core\IObject
{
    /**
     * This variable stores the code associated with the exception.
     *
     * @access protected
     * @var integer
     */
    protected $code;

    /**
     * This constructor creates a new Overflow Exception.
     *
     * @access public
     * @param string $message the error message
     * @param array $variables the translation variables
     * @param integer $code the exception code
     */
    public function __construct($message = '', array $variables = null, $code = 0)
    {
        parent::__construct(
            empty($variables) ? (string) $message : strtr((string) $message, $variables),
            (int) $code
        );
        $this->code = (int) $code; // Known bug: http://bugs.php.net/39615
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        unset($this->code);
    }

    /**
     * This method nicely writes out information about the object.
     *
     * @access public
     */
    public function __debug(): void
    {
        var_dump($this);
    }

    /**
     * This method evaluates whether the specified objects is equal to the current object.
     *
     * @access public
     * @param mixed $object the object to be evaluated
     * @return boolean whether the specified object is equal
     *                 to the current object
     */
    public function __equals($object)
    {
        return (($object !== null) && ($object instanceof Throwable\Overflow\Exception) && ((string) serialize($object) == (string) serialize($this)));
    }

    /**
     * This method returns the name of the called class.
     *
     * @access public
     * @return string the name of the called class
     */
    public function __getClass(): string
    {
        return get_called_class();
    }

    /**
     * This method returns the current object's hash code.
     *
     * @access public
     * @return string the current object's hash code
     */
    public function __hashCode(): string
    {
        return spl_object_hash($this);
    }

    /**
     * This function returns a string for this object.
     *
     * @access public
     * @override
     * @uses Throwable\Runtime\Exception::text
     * @return string the string for this object
     */
    public function __toString()
    {
        return Throwable\Runtime\Exception::text($this);
    }

}
