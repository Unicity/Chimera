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

use Unicity\IO;

/**
 * This class represent an object buffer.
 *
 * @access public
 * @class
 * @package IO
 */
class ObjectBuffer extends IO\File implements IO\Buffer
{
    /**
     * This variable stores the mime type of the file.
     *
     * @access protected
     * @var string
     */
    protected $mime;

    /**
     * This constructor initializes the class with an object.
     *
     * @access public
     * @param object $object the object to be buffered
     */
    public function __construct($object)
    {
        $this->name = null;
        $this->path = null;
        $this->ext = 'txt';

        $this->mime = 'application/x-php-serialized-object';
        $this->uri = static::buffer(serialize($object));
        $this->temporary = true;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->mime);
    }

    /**
     * This method returns the content type associated with the file.
     *
     * @access public
     * @return string the content type associated with
     *                the file
     */
    public function getContentType()
    {
        return $this->mime;
    }

    /**
     * This method returns the content type associated with the file based on the file's URI.
     *
     * @access public
     * @return string the content type associated with
     *                the file
     */
    public function getContentTypeFromName()
    {
        return $this->mime;
    }

    /**
     * This method returns the content type associated with the file based on the file's content.
     * Caution: Calling this method might cause side effects.
     *
     * @access public
     * @return string the content type associated with
     *                the file
     */
    public function getContentTypeFromStream()
    {
        return $this->mime;
    }

    /**
     * This function returns the extension associated with the file.
     *
     * @access public
     * @return string the extension for the file
     */
    public function getFileExtension()
    {
        return $this->ext;
    }

    /**
     * This function returns the extension associated with the file based on the file's URI.
     *
     * @access public
     * @return string the extension for the file
     *
     * @see http://www.php.net/manual/en/function.pathinfo.php
     */
    public function getFileExtensionFromName(): string
    {
        return $this->ext;
    }

    /**
     * This function returns the extension associated with the file based on the file's content.
     *
     * @access public
     * @return string the extension for the file
     */
    public function getFileExtensionFromStream(): string
    {
        return $this->ext;
    }

    /**
     * This method returns whether the file is executable.
     *
     * @access public
     * @return boolean
     */
    public function isExecutable(): bool
    {
        return false;
    }

    /**
     * This method returns whether the file is readable.
     *
     * @access public
     * @return boolean
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * This method returns whether the file is writable.
     *
     * @access public
     * @return boolean
     */
    public function isWritable(): bool
    {
        return false;
    }

}
