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

namespace Unicity\MappingService\Data;

use Unicity\MappingService;

/**
 * This class represents an associated array as an object.
 *
 * @access public
 * @class
 * @package MappingService
 */
class Field extends MappingService\Data\Metadata
{
    /**
     * This variable stores an array of serialized class objects, which is
     * used when type casting a result set.
     *
     * @access protected
     * @static
     * @var array
     */
    protected static $objects = [];

    /**
     * This variable stores the format type of the data.
     *
     * @access protected
     * @var \Unicity\MappingService\Data\FormatType the format type token associated
     *                                              with the data
     */
    protected $format;

    /**
     * This method initializes the class.
     *
     * @access public
     * @param \Unicity\MappingService\Data\FormatType $format the format type token associated
     *                                                        with the data
     * @param $items a traversable array or collection
     */
    public function __construct(MappingService\Data\FormatType $format, $items = null)
    {
        parent::__construct($items);
        $this->format = $format;
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
        return [$this->format, null];
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->format);
    }

    /**
     * This method returns format type associated with the data.
     *
     * @access public
     * @return \Unicity\MappingService\Data\FormatType the format type token associated
     *                                                 with the data
     */
    public function getFormatType(): \Unicity\MappingService\Data\FormatType
    {
        return $this->format;
    }

    /**
     * This method sets the data's format type.
     *
     * @param \Unicity\MappingService\Data\FormatType $format the format type token associated
     *                                                        with the data
     */
    public function setFormatType(MappingService\Data\FormatType $format)
    {
        $this->format = $format;
    }

    /**
     * This method return an instance of a canonical field.
     *
     * @access public
     * @return MappingService\Data\Field a new instance of the field
     */
    public static function ofCanonicalType()
    {
        if (!isset(static::$objects['canonical'])) {
            $object = new MappingService\Data\Field(MappingService\Data\FormatType::canonical());
            static::$objects['canonical'] = serialize($object);
        } else {
            $object = unserialize((string) static::$objects['canonical']);
        }

        return $object;
    }

    /**
     * This method return an instance of a model field.
     *
     * @access public
     * @return MappingService\Data\Field a new instance of the field
     */
    public static function ofModelType()
    {
        if (!isset(static::$objects['model'])) {
            $object = new MappingService\Data\Field(MappingService\Data\FormatType::model());
            static::$objects['model'] = serialize($object);
        } else {
            $object = unserialize((string) static::$objects['model']);
        }

        return $object;
    }

}
