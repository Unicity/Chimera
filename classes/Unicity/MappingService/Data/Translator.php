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

use Unicity\Common;
use Unicity\Core;
use Unicity\MappingService;
use Unicity\Throwable;

/**
 * This class is used to translate a data model using data translations.
 *
 * @abstract
 * @access public
 * @class
 * @package MappingService
 */
abstract class Translator extends Core\AbstractObject
{
    /**
     * This constant is used to represent a source translator.
     *
     * @access public
     * @const string
     */
    public const SOURCE_MAPPING = 'source';

    /**
     * This constant is used to represent a target translator.
     *
     * @access public
     * @const string
     */
    public const TARGET_MAPPING = 'target';

    /**
     * This variable stores any metadata associated with this translator.
     *
     * @access protected
     * @var array
     */
    protected $metadata;

    /**
     * This variable stores the models that are being processed.
     *
     * @access protected
     * @var Common\IMap
     */
    protected $models;

    /**
     * This variable stores an instance of the CreateCustomer translator.
     *
     * @access protected
     * @var Common\IMap
     */
    protected $translators;

    /**
     * This constructor initializes the class with the specified models and/or translators.
     *
     * @access public
     * @param Common\IMap $models the models to be used
     * @param Common\IMap $translators any additional translators to be used
     */
    public function __construct(Common\IMap $models = null, Common\IMap $translators = null)
    {
        $this->metadata = [];
        $this->models = ($models !== null) ? $models : new Common\Mutable\HashMap();
        $this->translators = ($translators !== null) ? $translators : new Common\Mutable\HashMap();
    }

    /**
     * This method is called when a method is not defined by this class and will attempt to remap
     * the call.
     *
     * @param string $method the method to be called
     * @param array $args the arguments associated with the call
     * @return mixed the result from making the call
     * @throws Throwable\UnimplementedMethod\Exception indicates that no method exists
     */
    public function __call($method, $args)
    {
        foreach ($this->translators as $translator) {
            if ($translator->__hasMethod($method)) {
                return call_user_func_array([$translator, $method], $args);
            }
        }

        throw new Throwable\UnimplementedMethod\Exception('Unable to call method. No method ":method" exists.', [':method' => $method]);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->metadata);
        unset($this->models);
        unset($this->translators);
    }

    /**
     * This method is call after all of the get or set methods have been called.
     *
     * @access public
     * @param string $capacity the capacity to which the translator
     *                         was used (i.e. as the source or as
     *                         the target)
     */
    public function __after($capacity)
    {
        // do nothing
    }

    /**
     * This method is call before all of the get or set methods are called.
     *
     * @access public
     * @param string $capacity the capacity to which the translator
     *                         will be used (i.e. as the source or
     *                         as the target)
     */
    public function __before($capacity)
    {
        // do nothing
    }

    /**
     * This method returns the property with the specified name.
     *
     * @access public
     * @param string $name the name of the property to be
     *                     returned
     * @return mixed the value of the property
     * @throws \Unicity\Throwable\InvalidProperty\Exception indicates that the property does
     *                                                      not exist
     */
    public function __get($name)
    {
        if (!in_array($name, ['metadata', 'models'])) {
            throw new Throwable\InvalidProperty\Exception('Unable to get property. Expected a valid name, but got ":name".', [':name' => $name]);
        }

        return $this->$name;
    }

    /**
     * This method returns whether the specified method exists.
     *
     * @access public
     * @param string $method the name of the method
     * @return boolean whether the specified method exists
     */
    public function __hasMethod(string $method): bool
    {
        if (method_exists($this, $method)) {
            $reflection = new \ReflectionMethod($this, $method);

            return $reflection->isPublic();
        }
        foreach ($this->translators as $translator) {
            if ($translator->__hasMethod($method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method sets the value associated with the the specified property name.
     *
     * @access public
     * @param string $name the name of the property
     * @param mixed $value the value of the property
     * @throws Throwable\InvalidProperty\Exception indicates that the property
     *                                             does not exist
     */
    public function __set($name, $value)
    {
        if (!in_array($name, ['metadata', 'models'])) {
            throw new Throwable\InvalidProperty\Exception('Unable to set property. Expected a valid name, but got ":name".', [':name' => $name]);
        }
        $this->$name = $value;
    }

    /**
     * This method maps the getter methods in the source model's translator to their corresponding setter
     * methods in the target model's translator.
     *
     * @access public
     * @static
     * @param \Unicity\MappingService\Data\Translator $source the model that will be the source for
     *                                                        the mapping
     * @param \Unicity\MappingService\Data\Translator $target the model that will be the target for
     *                                                        the mapping
     */
    public static function map(MappingService\Data\Translator $source, MappingService\Data\Translator $target)
    {
        $source->__before(static::SOURCE_MAPPING);

        $target->metadata =&$source->metadata;
        $target->__before(static::TARGET_MAPPING);

        $set = 'set';
        $get = 'get';

        if ($source->__getClass() == $target->__getClass()) {
            $set = 'u' . $set;
            $get = 'u' . $get;
        }

        $methods = $target->__getMethods();

        $setters = array_filter($methods, function ($method) use ($set) {
            return preg_match("/^{$set}[_a-zA-Z0-9]+$/", $method);
        });

        foreach ($setters as $setter) {
            $getter = $get . substr($setter, 3);
            if ($source->__hasMethod($getter)) {
                $target->$setter($source->$getter());
            }
        }

        $runners = array_filter($methods, function ($method) use ($set) {
            return preg_match('/^run[_a-zA-Z0-9]+$/', $method);
        });

        foreach ($runners as $runner) {
            $command = 'cmd' . substr($runner, 3);
            if ($source->__hasMethod($command)) {
                $target->$runner($source->$command());
            }
        }

        $target->__after(static::TARGET_MAPPING);
        $source->__after(static::SOURCE_MAPPING);
    }

}
