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

namespace Unicity\ORM\JSON\Model;

use Unicity\Bootstrap;
use Unicity\Common;
use Unicity\Config;
use Unicity\Core;
use Unicity\IO;
use Unicity\ORM;
use Unicity\Throwable;

/**
 * This class will build a model using a pre-defined JSON schema.
 *
 * @access public
 * @class
 * @package ORM
 *
 */
class Helper extends Core\AbstractObject
{
    /**
     * This method attempts to resolve the value as an array in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @param boolean $case_sensitive whether field names are case
     *                                sensitive
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveArrayValue($value, $definition, bool $case_sensitive)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        if (!($value instanceof ORM\JSON\Model\ArrayList)) {
            if (is_object($value) || is_array($value)) {
                $model = new ORM\JSON\Model\ArrayList($definition, $case_sensitive);
                $model->addValues(Core\Convert::toArray($value));
                $value = $model;
            } else {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array of type ":type0", but got a value of type ":type1".', [':type0' => '\\Unicity\\ORM\\JSON\\Model\\ArrayList', ':type1' => Core\DataType::info($value)->type]);
            }
        }

        if (isset($definition['minItems'])) {
            $size = $value->count();
            if ($size < $definition['minItems']) {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array that has a minimum size of ":minItems", but got ":size".', [':minItems' => $definition['minItems'], ':size' => $size]);
            }
        }

        if (isset($definition['maxItems'])) {
            $size = $value->count();
            if ($size > $definition['maxItems']) {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected an array that has a maximum size of ":maxItems", but got ":size".', [':maxItems' => $definition['maxItems'], ':size' => $size]);
            }
        }

        return $value;
    }

    /**
     * This method attempts to resolve the value as a boolean in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveBooleanValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        $value = Core\Convert::toBoolean($value);

        return $value;
    }

    /**
     * This method stores the JSON schemas already loaded.
     *
     * @access private
     * @static
     * @var array
     */
    private static $schemas = [];

    /**
     * This method returns the JSON schema for the specified input.
     *
     * @access public
     * @static
     * @param mixed $schema the JSON schema to be loaded
     * @return array the JSON schema
     */
    public static function resolveJSONSchema($schema)
    {
        if ($schema instanceof Common\StringRef) {
            $schema = Core\Convert::toString($schema);
        }

        $key = md5(serialize($schema));

        if (!array_key_exists($key, static::$schemas)) {
            static::$schemas[$key] = static::_resolveJSONSchema($schema);
        }

        return static::$schemas[$key];
    }

    /**
     * This method returns the JSON schema for the specified input.
     *
     * @access private
     * @static
     * @param mixed $schema the JSON schema to be loaded
     * @return array the JSON schema
     */
    private static function _resolveJSONSchema($schema)
    {
        if (is_string($schema)) {
            $components = preg_split('/(\\\|_)+/', trim($schema, '\\'));

            $fileName = implode(DIRECTORY_SEPARATOR, $components) . '.json';

            foreach (Bootstrap::$classpaths as $directory) {
                $uri = Bootstrap::rootPath() . $directory . $fileName;
                if (file_exists($uri)) {
                    $schema = Config\JSON\Reader::load(new IO\File($uri))->read();

                    break;
                }
                $uri = $directory . $fileName;
                if (file_exists($uri)) {
                    $schema = Config\JSON\Reader::load(new IO\File($uri))->read();

                    break;
                }
            }
        }
        $schema = Core\Convert::toDictionary($schema);
        if (isset($schema['$ref'])) {
            $result = static::_resolveJSONSchema($schema['$ref']);
            if (isset($schema['properties'])) {
                $result = array_merge($result, $schema['properties']);
            }

            return $result;
        }

        return $schema;
    }

    /**
     * This method attempts to resolve the value as an integer in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveIntegerValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        try {
            $value = Core\Convert::toInteger($value);
        } catch (\Throwable $ex) {
            $value = 0;
        }

        if (is_integer($value)) {
            if (isset($definition['exclusiveMinimum']) && $definition['exclusiveMinimum']) {
                if ($value <= $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            } elseif (isset($definition['minimum'])) {
                if ($value < $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than or equal to ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            }

            if (isset($definition['exclusiveMaximum']) && $definition['exclusiveMaximum']) {
                if ($value >= $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            } elseif (isset($definition['maximum'])) {
                if ($value > $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than or equal to ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            }

            if (isset($definition['divisibleBy'])) {
                if (($value % $definition['divisibleBy']) == 0) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is divisible by ":divisibleBy", but got :value.', [':divisibleBy' => $definition['divisibleBy'], ':value' => $value]);
                }
            }

            if (isset($definition['pad']['length'])) {
                $value = Core\Convert::toString($value);
                if (strlen($value) < $definition['pad']['length']) {
                    $char = isset($definition['pad']['char']) ? $definition['pad']['char'] : '0';
                    $value = str_pad($value, $definition['pad']['length'], $char, STR_PAD_LEFT);
                }
            }

            if (isset($definition['enum']) && (count($definition['enum']) > 0)) {
                if (!in_array($value, $definition['enum'])) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is in enumeration, but got :value.', [':value' => $value]);
                }
            }
        }

        return $value;
    }

    /**
     * This method attempts to resolve the value as a mixed value in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     */
    public static function resolveMixedValue($value, $definition)
    {
        return $value;
    }

    /**
     * This method attempts to resolve the value as money in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveMoneyValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        try {
            $value = Core\Convert::toDouble($value);
        } catch (\Throwable $ex) {
            $value = 0.0;
        }

        if (is_double($value)) {
            if (isset($definition['exclusiveMinimum']) && $definition['exclusiveMinimum']) {
                if ($value <= $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            } elseif (isset($definition['minimum'])) {
                if ($value < $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than or equal to ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            }

            if (isset($definition['exclusiveMaximum']) && $definition['exclusiveMaximum']) {
                if ($value >= $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            } elseif (isset($definition['maximum'])) {
                if ($value > $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than or equal to ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            }

            if (isset($definition['divisibleBy'])) {
                if (fmod($value, $definition['divisibleBy']) == 0.0) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is divisible by ":divisibleBy", but got :value.', [':divisibleBy' => $definition['divisibleBy'], ':value' => $value]);
                }
            }

            if (isset($definition['enum']) && (count($definition['enum']) > 0)) {
                if (!in_array($value, $definition['enum'])) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is in enumeration, but got :value.', [':value' => $value]);
                }
            }
        }

        return number_format(floatval($value), 2, '.', '');
    }

    /**
     * This method attempts to resolve the value as a number in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveNumberValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        try {
            $value = Core\Convert::toDouble($value);
        } catch (\Throwable $ex) {
            $value = 0.0;
        }

        if (is_double($value)) {
            if (isset($definition['exclusiveMinimum']) && $definition['exclusiveMinimum']) {
                if ($value <= $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            } elseif (isset($definition['minimum'])) {
                if ($value < $definition['minimum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is greater than or equal to ":minimum", but got :value.', [':minimum' => $definition['minimum'], ':value' => $value]);
                }
            }

            if (isset($definition['exclusiveMaximum']) && $definition['exclusiveMaximum']) {
                if ($value >= $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            } elseif (isset($definition['maximum'])) {
                if ($value > $definition['maximum']) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is less than or equal to ":maximum", but got :value.', [':maximum' => $definition['maximum'], ':value' => $value]);
                }
            }

            if (isset($definition['divisibleBy'])) {
                if (fmod($value, $definition['divisibleBy']) == 0.0) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is divisible by ":divisibleBy", but got :value.', [':divisibleBy' => $definition['divisibleBy'], ':value' => $value]);
                }
            }

            if (isset($definition['enum']) && (count($definition['enum']) > 0)) {
                if (!in_array($value, $definition['enum'])) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is in enumeration, but got :value.', [':value' => $value]);
                }
            }
        }

        return $value;
    }

    /**
     * This method attempts to resolve the value as a number/string in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveNumberOrStringValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        try {
            if ($value instanceof Common\StringRef) {
                $value = $value->__toString();
            }
            if (is_string($value) && (($value === '') || !is_numeric($value))) {
                $value = Core\Convert::toString($value);
            } else {
                $value = Core\Convert::toDouble($value);
            }
        } catch (\Throwable $ex) {
            $value = Core\Convert::toString($value);
        }

        if (isset($definition['pattern'])) {
            if (!preg_match($definition['pattern'], (string) $value)) {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value matching pattern ":pattern", but got :value.', [':pattern' => $definition['pattern'], ':value' => $value]);
            }
        }

        if (isset($definition['enum']) && (count($definition['enum']) > 0)) {
            if (!in_array($value, $definition['enum'])) {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is in enumeration, but got :value.', [':value' => $value]);
            }
        }

        return $value;
    }

    /**
     * This method attempts to resolve the value as a null in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveNullValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUndefined($value)) {
            return $value;
        }

        return null;
    }

    /**
     * This method attempts to resolve the value as an object in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @param boolean $case_sensitive whether field names are case
     *                                sensitive
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveObjectValue($value, $definition, bool $case_sensitive)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        if (!($value instanceof ORM\JSON\Model\HashMap)) {
            if (is_object($value) || is_array($value)) {
                $model = new ORM\JSON\Model\HashMap($definition, $case_sensitive);
                $model->putEntries(Core\Convert::toDictionary($value));
                $value = $model;
            } else {
                throw new Throwable\Runtime\Exception('Invalid value defined. Expected an object of type ":type0", but got a value of type ":type1".', [':type0' => '\\Unicity\\ORM\\JSON\\Model\\HashMap', ':type1' => Core\DataType::info($value)->type]);
            }
        }

        return $value;
    }

    /**
     * This method attempts to resolve the value as a string in accordance with the schema
     * definition.
     *
     * @access public
     * @static
     * @param mixed $value the value to be resolved
     * @param array $definition the schema definition
     * @return mixed the resolved value
     * @throws Throwable\Runtime\Exception indicates that the value failed
     *                                     to meet a requirement
     */
    public static function resolveStringValue($value, $definition)
    {
        if (Core\Data\ToolKit::isUnset($value)) {
            return $value;
        }

        $value = Core\Convert::toString($value);

        $delimiter = $definition['delimiter'] ?? '';

        $options = ($delimiter !== '') ? array_map('trim', explode($delimiter, $value)) : [$value];

        foreach ($options as &$option) {
            if (isset($definition['filters'])) {
                foreach ($definition['filters'] as $filter) {
                    $option = Core\Convert::toString(call_user_func($filter, $option));
                }
            }

            if (isset($definition['pattern'])) {
                if (!preg_match($definition['pattern'], $option)) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value matching pattern ":pattern", but got :value.', [':pattern' => $definition['pattern'], ':value' => $option]);
                }
            }

            if (isset($definition['minLength'])) {
                if (strlen($option) < $definition['minLength']) {
                    $option = str_pad($option, $definition['minLength'], ' ', STR_PAD_RIGHT);
                }
            }

            if (isset($definition['maxLength'])) {
                if (strlen($option) > $definition['maxLength']) {
                    $option = substr($option, 0, $definition['maxLength']);
                    // TODO log string was truncated
                }
            }

            if (isset($definition['enum']) && (count($definition['enum']) > 0)) {
                if (!in_array($option, $definition['enum'])) {
                    throw new Throwable\Runtime\Exception('Invalid value defined. Expected a value that is in enumeration, but got :value.', [':value' => $option]);
                }
            }
        }

        return implode($delimiter, $options);
    }

}
