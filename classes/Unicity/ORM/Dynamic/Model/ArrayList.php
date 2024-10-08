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

namespace Unicity\ORM\Dynamic\Model;

use Unicity\Common;
use Unicity\Core;
use Unicity\ORM;
use Unicity\Throwable;

/**
 * This class represents an array list.
 *
 * @access public
 * @class
 * @package ORM
 */
class ArrayList extends Common\Mutable\ArrayList implements ORM\IModel
{
    /**
     * This variable stores whether field names are case sensitive.
     *
     * @access protected
     * @var boolean
     */
    protected $case_sensitive;

    /**
     * This method initializes the class with the specified values (if any are provided).
     *
     * @access public
     * @param $elements a traversable array or collection
     * @param boolean $case_sensitive whether field names are case
     *                                sensitive
     */
    public function __construct($elements = null, bool $case_sensitive = true)
    {
        $this->case_sensitive = $case_sensitive;
        parent::__construct($elements);
    }

    /**
     * This method returns an array of arguments for constructing another collection
     * via function programming.
     *
     * @access public
     * @return array the argument array for initialization
     */
    public function __constructor_args(): array
    {
        return [null, $this->case_sensitive];
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->case_sensitive);
    }

    /**
     * This method returns the schema associated with this model.
     *
     * @access public
     * @return array the model's schema
     */
    public function getSchema()
    {
        return null;
    }

    /**
     * This method returns the element at the the specified index.
     *
     * @access public
     * @param integer $index the index of the element
     * @return mixed the element at the specified index
     * @throws Throwable\InvalidArgument\Exception indicates that an index must be an integer
     * @throws Throwable\OutOfBounds\Exception indicates that the index is out of bounds
     */
    public function getValue($index)
    {
        if (!is_integer($index)) {
            throw new Throwable\InvalidArgument\Exception('Unable to get element. :type is of the wrong data type.', [':type' => Core\DataType::info($index)->type]);
        }
        if (array_key_exists($index, $this->elements)) {
            return $this->elements[$index];
        }

        return Core\Data\Undefined::instance();
    }

}
