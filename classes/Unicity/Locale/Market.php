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

namespace Unicity\Locale;

use Unicity\Locale;

/**
 * This class represents a market.
 *
 * @access public
 * @abstract
 * @class
 * @package Locale
 */
abstract class Market extends Locale\Country
{
    /**
     * This variable stores the market unit.
     *
     * @access protected
     * @var array
     */
    protected $unit;

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->unit);
    }

    /**
     * This method returns the market id.
     *
     * @access public
     * @abstract
     * @param integer $n the maximum number of parts
     * @return string the market id
     */
    abstract public function getMarketId($n = 0);

    /**
     * This method returns the market unit.
     *
     * @access public
     * @return string
     */
    public function getMarketUnit()
    {
        return $this->unit;
    }

    /**
     * This method returns the market id.
     *
     * @access public
     * @return string the market id
     */
    public function __toString()
    {
        return $this->getMarketId();
    }

}
