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

namespace Unicity\IO;

use Unicity\Core;
use Unicity\IO;

/**
 * This class represents a data writer.
 *
 * @abstract
 * @access public
 * @class
 * @package IO
 */
abstract class Writer extends Core\AbstractObject implements Core\IDisposable
{
    /**
     * This method closes the resource.
     *
     * @access public
     * @abstract
     * @return IO\Writer a reference to this class
     */
    abstract public function close(): IO\Writer;

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        $this->dispose();
    }

    /**
     * This method assists with freeing, releasing, and resetting un-managed resources.
     *
     * @access public
     * @param boolean $disposing whether managed resources can be
     *                           disposed in addition to un-managed
     *                           resources
     */
    public function dispose(bool $disposing = true): void
    {
        $this->close();
    }

    /**
     * This method opens the resource.
     *
     * @access public
     * @abstract
     */
    abstract public function open(): IO\Writer;

    /**
     * This method write the data to the resource.
     *
     * @access public
     * @abstract
     * @param mixed $data the data to be written
     * @return IO\Writer a reference to this class
     */
    abstract public function write($data): IO\Writer;

    /**
     * This method write the data, plus an end of line character, to the resource.
     *
     * @access public
     * @abstract
     * @param mixed $data the data to be written
     * @return IO\Writer a reference to this class
     */
    abstract public function writeLine($data): IO\Writer;

    /**
     * This method writes the contents of a collection to the resource.
     *
     * @access public
     * @abstract
     * @param mixed $collection a collection of data to be written
     * @return IO\Writer a reference to this class
     */
    abstract public function writeLines($collection): IO\Writer;

}
