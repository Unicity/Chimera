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

namespace Unicity\Config\CSV;

use Unicity\Common;
use Unicity\Config;
use Unicity\Core;
use Unicity\IO;
use Unicity\Throwable;

/**
 * This class is used to build a collection from a CSV file.
 *
 * @access public
 * @class
 * @package Config
 */
class Reader extends Config\Reader
{
    /**
     * This constructor initializes the class with the specified resource.
     *
     * @access public
     * @param IO\File $file the file to be processed
     * @param array $metadata the metadata to be set
     */
    public function __construct(IO\File $file, array $metadata = [])
    {
        $this->file = $file;
        $this->metadata = array_merge([
            'bom' => false, // whether to remove BOM from the first line
            'delimiter' => ',',
            'enclosure' => '"',
            'encoder' => null,
            'encoding' => [Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING],
            'escape' => '\\',
            'filter' => null, // {{ class_name }}
            'headers' => [],
            'key_case' => null, // null || CASE_LOWER || CASE_UPPER
            'list_type' => '\\Unicity\\Common\\Mutable\\ArrayList',
            'schema' => [],
            'strict_mapping' => true,
            'strip_invalid_chars' => false,
        ], $metadata);
        if ($this->metadata['delimiter'] == '\\t') {
            $this->metadata['delimiter'] = "\t";
        }
    }

    /**
     * This method iterates over each record in the file, yielding each item to the procedure function.
     *
     * @access public
     * @param callable $procedure the procedure function to be used
     * @throws Throwable\Parse\Exception indicates that an invalid record was
     *                                   encountered
     */
    public function each(callable $procedure)
    {
        $self = $this;

        $headers = (isset($this->metadata['headers']) && is_array($this->metadata['headers']))
            ? $this->metadata['headers']
            : [];

        IO\FileReader::read($this->file, function ($reader, $data, $index) use ($self, $procedure, &$headers) {
            $line = trim((string) $data);
            if (strlen($line) > 0) {
                if (($index == 0) && (count($headers) == 0)) {
                    if ($self->bom) {
                        $line = preg_replace('/^' . pack('H*', 'EFBBBF') . '/', '', $line);
                    }
                    $headers = str_getcsv($line, $self->delimiter, $self->enclosure, $self->escape);
                    $headers = array_map('trim', $headers);
                    if ($self->key_case !== null) {
                        switch ($self->key_case) {
                            case CASE_LOWER:
                                $headers = array_map('strtolower', $headers);

                                break;
                            case CASE_UPPER:
                                $headers = array_map('strtoupper', $headers);

                                break;
                        }
                    }
                } else {
                    $record = str_getcsv($line, $self->delimiter, $self->enclosure, $self->escape);

                    if (!is_array($record)) {
                        throw new Throwable\Runtime\Exception('Failed to process record. Expected an array, but got ":type".', [':type' => gettype($record)]);
                    }

                    $record = array_combine($headers, $record);

                    if ($self->strict_mapping && (count($headers) != count($record))) {
                        throw new Throwable\Runtime\Exception('Failed to process record. Header count does not match column count.');
                    }

                    $source_encoding = ($self->encoder !== null) ? call_user_func($self->encoder . '::getEncoding', $record) : $self->encoding[0];
                    $target_encoding = $self->encoding[1];

                    foreach ($record as $key => &$value) {
                        $value = Core\Data\Charset::encode($value, $source_encoding, $target_encoding);
                        if ($self->strip_invalid_chars) {
                            $value = $self->removeNonUTF8Characters($value);
                        }
                        $type = (isset($self->schema[$key])) ? $self->schema[$key] : 'string';
                        $value = Core\Convert::changeType($value, $type);
                    }

                    $map = new Common\Mutable\HashMap($record);
                    if (($self->filter === null) || call_user_func_array([$self->filter, 'isQualified'], [$map])) {
                        $procedure($map);
                    }
                }
            }
        });
    }

    /**
     * This method returns the processed resource as a collection.
     *
     * @access public
     * @param string $path the path to the value to be returned
     * @return mixed the resource as a collection
     * @throws Throwable\Runtime\Exception indicates that an invalid record was
     *                                     encountered
     */
    public function read($path = null)
    {
        if ($this->file->getFileSize() > 0) {
            $list = $this->metadata['list_type'];
            $collection = new $list();

            $this->each(function (Common\Mutable\HashMap $record) use ($collection) {
                $collection->addValue($record);
            });

            if ($path !== null) {
                try {
                    $path = Core\Convert::toString($path);
                    $collection = Config\Helper::factory($collection)->getValue($path);
                } catch (\Throwable $ex) {
                    return null;
                }
            }

            return $collection;
        }

        return null;
    }

    /**
     * This method returns a new string resulting from stripping out all non-UTF-8 characters from
     * the specified string.
     *
     * @access public
     * @param string $string the string to be processed
     * @return string a new string with all non-UTF-8 characters
     *                removed
     */
    public function removeNonUTF8Characters($string)
    {
        return preg_replace('/[^\P{C}\s]/', '', $string);
    }

}
