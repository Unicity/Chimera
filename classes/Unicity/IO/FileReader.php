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
use Unicity\Throwable;

/**
 * This class handles how a file is read.
 *
 * @access public
 * @class
 * @package IO
 *
 * @see http://www.ibm.com/developerworks/library/os-php-readfiles/index.html?ca=drs
 */
class FileReader extends IO\Reader
{
    /**
     * This variable stores a reference to the data source.
     *
     * @access protected
     * @var IO\File
     */
    protected $file;

    /**
     * This variable stores a reference to the resource.
     *
     * @access protected
     * @var resource
     */
    protected $handle;

    /**
     * This constructor instantiates the class.
     *
     * @access public
     * @param IO\File $file the file to be opened
     * @throws Throwable\FileNotFound\Exception indicates the file could not be
     *                                          opened
     * @throws Throwable\InvalidArgument\Exception indicates the file is not readable
     */
    public function __construct(IO\File $file)
    {
        if (!$file->exists()) {
            throw new Throwable\FileNotFound\Exception('Unable to open file :file.', [':file' => $file]);
        }
        if (!$file->isReadable()) {
            throw new Throwable\InvalidArgument\Exception('Unable to read from file. File ":file" is not readable.', [':file' => $file]);
        }
        $this->file = $file;
        $this->handle = null;
        $this->open();
    }

    /**
     * This method closes the reader.
     *
     * @access public
     */
    public function close(): void
    {
        if ($this->handle !== null) {
            if (is_resource($this->handle)) {
                @fclose($this->handle);
            }
            $this->handle = null;
        }
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->file);
        unset($this->handle);
    }

    /**
     * This method returns whether the reader is done reading.
     *
     * @access public
     * @return boolean whether the reader is done
     *                 reading
     */
    public function isDone(): bool
    {
        return !$this->isReady();
    }

    /**
     * This method returns whether the reader is ready to read.
     *
     * @access public
     * @return boolean whether the reader is ready
     *                 to read
     */
    public function isReady(): bool
    {
        return (($this->handle !== null) && !feof($this->handle));
    }

    /**
     * This method returns the length of the resource.
     *
     * @access public
     * @return integer the length of the resource
     */
    public function length(): int
    {
        return $this->file->getFileSize();
    }

    /**
     * This method opens the reader.
     *
     * @access public
     */
    public function open(): void
    {
        if ($this->handle === null) {
            $this->handle = @fopen((string) $this->file, 'r');
            $this->mark = 0;
        }
    }

    /**
     * This method returns the current position of the reader.
     *
     * @access public
     * @return integer the current position of the
     *                 reader
     */
    public function position(): int
    {
        return ftell($this->handle);
    }

    /**
     * This method returns a block of characters in the resource.
     *
     * @access public
     * @param integer $offset the offset position to start
     *                        reading at
     * @param integer $length the number of character to read
     * @return string the block of characters in the
     *                resource
     */
    public function readBlock(int $offset, int $length): ?string
    {
        if (!$this->isDone() && ($length > 0)) {
            $this->seek($offset);
            $buffer = '';
            for ($i = 0; !$this->isDone() && $i < $length; $i++) {
                $char = fgetc($this->handle);
                if (is_string($char)) {
                    $buffer .= $char;
                }
            }
            if (strlen($buffer) > 0) {
                return $buffer;
            }
        }

        return null;
    }

    /**
     * This method returns a character from the resource.
     *
     * @access public
     * @param integer $position the position from which to read
     * @param boolean $advance whether to advance the position
     *                         after the read
     * @return string the next character in the resource
     */
    public function readChar($position = null, bool $advance = true): ?string
    {
        if (($position !== null) && is_integer($position)) {
            $this->seek($position);
            $char = $this->readChar(null, $advance);

            return $char;
        }
        if (!$this->isDone()) {
            $position = $this->position();
            $char = fgetc($this->handle);
            if (!$advance) {
                $this->seek($position);
            }
            if (is_string($char)) {
                return $char;
            }
        }

        return null;
    }

    /**
     * This method returns the next line in the resource.
     *
     * @access public
     * @return string the next line in the resource
     */
    public function readLine(): ?string
    {
        if (!$this->isDone()) {
            ini_set('auto_detect_line_endings', '1');
            $line = fgets($this->handle);
            if (is_string($line)) {
                return $line;
            }
        }

        return null;
    }

    /**
     * This method returns all characters from the current position to the end of the stream.
     *
     * @access public
     * @return string all characters from the current
     *                position to the end of the stream
     */
    public function readToEnd(): ?string
    {
        if (!$this->isDone()) {
            $buffer = '';
            do {
                $buffer .= fgets($this->handle);
            } while (!$this->isDone());
            if (strlen($buffer) > 0) {
                return $buffer;
            }
        }

        return null;
    }

    /**
     * This method resets the reader to the last marked position.
     *
     * @access public
     */
    public function reset()
    {
        fseek($this->handle, $this->mark);
    }

    /**
     * This method moves the reader to specified position.
     *
     * @access public
     * @param integer $position the seek position
     */
    public function seek($position): void
    {
        fseek($this->handle, (int) $position);
    }

    /**
     * This method skips to "n" positions ahead.
     *
     * @access public
     * @param integer $n the number of positions to skip
     */
    public function skip($n): void
    {
        fseek($this->handle, $this->position() + $n);
    }

    /**
     * This method provides a declaratory means of reading a file.
     *
     * @access public
     * @static
     * @param IO\File $file the file to be opened
     * @param callable $callback the callback function that will
     *                           handle the read
     * @param string $mode the mode in which to read the file
     * @throws Throwable\InvalidArgument\Exception indicates an invalid argument specified
     * @throws \Exception indicates a rethrown exception
     */
    public static function read(IO\File $file, callable $callback, string $mode = 'readLine'): void
    {
        if (!in_array($mode, ['readChar', 'readLine', 'readToEnd'])) {
            throw new Throwable\InvalidArgument\Exception('Invalid argument specified. Expected mode to be either "read", "readLine", or "readToEnd", but got :mode.', [':mode' => $mode]);
        }
        $reader = null;

        try {
            $reader = new self($file);
            $index = 0;
            while ($reader->isReady()) {
                $data = $reader->$mode();
                if (is_string($data)) {
                    $callback($reader, $data, $index);
                    $index++;
                }
            }
            $reader->close();
        } catch (\Throwable $ex) {
            if ($reader != null) {
                $reader->close();
            }

            throw $ex;
        }
    }

}
