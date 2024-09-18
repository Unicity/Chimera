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

namespace Unicity\Pipeline;

final class Payload
{
    /**
     * This variable stores the arguments.  In general, arguments should not be modified once
     * set (i.e. they should be treated as read-only).  For example, this array might be used
     * to store the request's arguments.
     *
     * @access private
     * @var array a map of arguments.
     */
    private $arguments;

    /**
     * This variable stores a reference to an exception should any been thrown.
     *
     * @access private
     * @var \Exception a thrown exception
     */
    private $exception;

    /**
     * This variable stores the message.
     *
     * @access private
     * @var mixed a message
     */
    private $message;

    /**
     * This variable stores any properties that should be passed along with the message.
     *
     * @access private
     * @var array a map of properties
     */
    private $properties;

    /**
     * This constructor initialize this class.
     *
     * @access public
     * @param array $arguments a map of arguments
     * @param array $properties a map of properties
     */
    public function __construct(array $arguments = [], array $properties = [])
    {
        $this->arguments = $arguments;
        $this->exception = null;
        $this->message = null;
        $this->properties = $properties;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        unset($this->arguments);
        unset($this->exception);
        unset($this->message);
        unset($this->properties);
    }

    /**
     * This method returns the value of the argument with the given name.
     *
     * @access public
     * @param string $name the name of the argument
     * @param mixed $default the default value if argument does not exist
     * @return mixed the value of the argument for the
     *               given name
     */
    public function getArgument($name, $default = null)
    {
        if (isset($this->arguments[$name])) {
            return $this->arguments[$name];
        }

        return $default;
    }

    /**
     * This method returns the arguments as an array.
     *
     * @access public
     * @return array the arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * This method returns an exception that was previously set.
     *
     * @access public
     * @return \Exception the thrown exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * This method returns the message.
     *
     * @access public
     * @param mixed $default the default value if no message exist
     * @return mixed the message
     */
    public function getMessage($default = null)
    {
        return ($this->message !== null) ? $this->message : $default;
    }

    /**
     * This method returns the value of the property with the given name.
     *
     * @access public
     * @param string $name the name of the property
     * @param mixed $default the default value if property does not exist
     * @return mixed the value of the property for the
     *               given name
     */
    public function getProperty($name, $default = null)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return $default;
    }

    /**
     * This method returns the properties as an array.
     *
     * @access public
     * @return array the properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * This method returns whether there is an argument with the given name.
     *
     * @access public
     * @param string $name the name of the argument
     * @return boolean whether there is an argument with
     *                 the given name
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * This method returns whether there is an exception set.
     *
     * @access public
     * @return boolean whether there is an exception set
     */
    public function hasException()
    {
        return ($this->exception !== null);
    }

    /**
     * This method returns whether there is a message set.
     *
     * @access public
     * @return boolean whether there is a message set
     */
    public function hasMessage()
    {
        return ($this->exception !== null);
    }

    /**
     * This method returns whether there is a property with the given name.
     *
     * @access public
     * @param string $name the name of the property
     * @return boolean whether there is a property with
     *                 the given name
     */
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * This method is used to set an argument's value.
     *
     * @access public
     * @param string $name the name of the argument to be set
     * @param mixed $value the value of the argument to be set
     * @return Payload a reference to this class
     */
    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * This method sets the arguments.
     *
     * @access public
     * @param array $arguments the arguments to be set
     * @return Payload a reference to this class
     */
    public function setArguments(array $arguments)
    {
        foreach ($arguments as $name => $value) {
            $this->setArgument($name, $value);
        }

        return $this;
    }

    /**
     * This method sets the specified exception as the one thrown by the concern.
     *
     * @access public
     * @param \Exception $exception the exception to be set
     * @return Payload a reference to this class
     */
    public function setException(\Exception $exception = null)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * This method sets the value as that returned by the concern.
     *
     * @access public
     * @param mixed $message the message to be set
     * @return Payload a reference to this class
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * This method is used to set a property's value.
     *
     * @access public
     * @param string $name the name of the property to be set
     * @param mixed $value the value of the property to be set
     * @return Payload a reference to this class
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * This method sets the properties.
     *
     * @access public
     * @param array $properties the properties to be set
     * @return Payload a reference to this class
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }

        return $this;
    }

}
