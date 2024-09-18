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

namespace Unicity\EVT\EventWriter;

use Unicity\EVT;
use Unicity\Throwable;

class File extends EVT\EventWriter
{
    /**
     * This variable stores the URI of the directory where events will be written.
     *
     * @access protected
     * @var string
     */
    protected $directory;

    /**
     * This constructor initializes the class with the specified resource.
     *
     * @access public
     * @param string $directory the URI of the directory where events
     *                          will be written
     * @param array $metadata the metadata to be set
     *
     * @throws Throwable\FileNotFound\Exception indicates the directory could not be
     *                                          written to by the writer
     */
    public function __construct(string $directory, array $metadata = [])
    {
        parent::__construct(array_merge([
            'eol' => "\n",
            'file' => '',
        ], $metadata));

        if (!is_dir($directory) || !is_writable($directory)) {
            throw new Throwable\FileNotFound\Exception('Directory :directory must be writable', [':directory' => $directory]);
        }

        $this->directory = realpath($directory) . DIRECTORY_SEPARATOR;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->directory);
    }

    /**
     * This method writes an array of events to the event storage.
     *
     * @access public
     * @param array $events the events to be written
     */
    public function write(array $events)
    {
        $uri = $this->metadata['file'];

        if (empty($uri)) {
            $directory = $this->directory . date('Y');
            if (!is_dir($directory)) {
                mkdir($directory, 02777);
                chmod($directory, 02777);
            }

            $directory .= DIRECTORY_SEPARATOR . date('m');
            if (!is_dir($directory)) {
                mkdir($directory, 02777);
                chmod($directory, 02777);
            }

            $uri = $directory . DIRECTORY_SEPARATOR . date('d') . '.txt';
        } else {
            $uri = $this->directory . $uri;
        }

        if (!file_exists($uri)) {
            file_put_contents($uri, '');
            chmod($uri, 0666); // makes file writable
        }

        foreach ($events as $event) {
            file_put_contents($uri, json_encode($event) . $this->metadata['eol'], FILE_APPEND);
        }
    }

}
