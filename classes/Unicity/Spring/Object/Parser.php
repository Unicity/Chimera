<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011 Spadefoot Team
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

namespace Unicity\Spring\Object;

use Unicity\Common;
use Unicity\Core;
use Unicity\Spring;
use Unicity\Throwable;

class Parser extends Core\AbstractObject
{
    /**
     * This variable stores a list of valid primitive types.
     *
     * @access protected
     * @static
     * @var array
     */
    protected static $primitives = [
        'bool', 'boolean',
        'char',
        'date', 'datetime', 'time', 'timestamp',
        'decimal', 'double', 'float', 'money', 'number', 'real', 'single',
        'bit', 'byte', 'int', 'int8', 'int16', 'int32', 'int64', 'long', 'short', 'uint', 'uint8', 'uint16', 'uint32', 'uint64', 'integer', 'word',
        'ord', 'ordinal',
        'nil', 'null',
        'nvarchar', 'string', 'varchar', 'undefined',
    ];

    /**
     * This variable stores the application context.
     *
     * @access protected
     * @var Spring\XMLObjectFactory
     */
    protected $context;

    /**
     * This variable keeps track of idrefs to help prevent circular references.
     *
     * @access protected
     * @var array
     */
    protected $idrefs;

    /**
     * This variable stores the registry for parsing XML Spring objects.
     *
     * @access protected
     * @var Spring\Object\Registry
     */
    protected $registry;

    /**
     * This variable stores an array of singleton instances that were initialized when
     * parsing.
     *
     * @access protected
     * @var Common\Mutable\IMap
     */
    protected $singletons;

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param Spring\XMLObjectFactory $context the XML resource to be parsed
     */
    public function __construct(Spring\XMLObjectFactory $context)
    {
        $this->context = $context;

        $this->idrefs = [];

        $this->registry = new Spring\Object\Registry();
        $this->registry->putEntry(['array', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\ArrayElement());
        $this->registry->putEntry(['dictionary', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\DictionaryElement());
        $this->registry->putEntry(['entry', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\EntryElement());
        $this->registry->putEntry(['expression', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\ExpressionElement());
        $this->registry->putEntry(['function', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\FunctionElement());
        $this->registry->putEntry(['idref', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\IdrefElement());
        $this->registry->putEntry(['list', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\ListElement());
        $this->registry->putEntry(['map', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\MapElement());
        $this->registry->putEntry(['null', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\NullElement());
        $this->registry->putEntry(['object', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\ObjectElement());
        $this->registry->putEntry(['ref', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\RefElement());
        $this->registry->putEntry(['set', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\SetElement());
        $this->registry->putEntry(['undefined', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\UndefinedElement());
        $this->registry->putEntry(['value', Spring\Data\XML::NAMESPACE_URI], new Spring\Object\Factory\ValueElement());

        $this->singletons = new Common\Mutable\HashMap();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->context);
        unset($this->idrefs);
        unset($this->registry);
        unset($this->singletons);
    }

    /**
     * This method returns an array of nodes matching the specified id.
     *
     * @access public
     * @param string $idref the object's id
     * @return array an array of nodes with the specified
     *               id
     * @throws Throwable\InvalidArgument\Exception indicates that an argument is invalid
     *
     * @see http://stackoverflow.com/questions/1257867/regular-expressions-and-xpath-query
     */
    public function find($idref)
    {
        if (!$this->isId($idref)) {
            throw new Throwable\InvalidArgument\Exception('Invalid argument detected (id: :id).', [':id' => $idref]);
        }
        $resource = $this->getResource();
        $resource->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
        $elements = $resource->xpath("/spring:objects/*[@id='{$idref}' or contains(@name,'{$idref}')]");
        $elements = array_filter($elements, function (\SimpleXMLElement $element) use ($idref) {
            $attributes = $this->getElementAttributes($element);

            return ((isset($attributes['id']) && ($this->valueOf($attributes['id']) == $idref)) || (isset($attributes['name']) && in_array($idref, preg_split('/(,|;|\s)+/', $this->valueOf($attributes['name'])))));
        });

        return $elements;
    }

    /**
     * This method returns the element's attributes.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @param string $namespace the namespace associated with
     *                          the attributes
     * @return array the attributes
     */
    public function getElementAttributes(\SimpleXMLElement $element, $namespace = '')
    {
        if (is_string($namespace)) {
            if ($namespace != '') {
                return $element->attributes($namespace);
            }

            return $element->attributes();
        }

        return $element->attributes(); // TODO make like "getElementChildren"
    }

    /**
     * This method returns the element's children.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @param string $namespace the namespace associated with
     *                          the children
     * @return array the element's children
     *
     * @see http://php.net/manual/en/class.simplexmlelement.php
     */
    public function getElementChildren(\SimpleXMLElement $element, $namespace = '')
    {
        if (is_string($namespace)) {
            if ($namespace != '') {
                return $element->children($namespace);
            }

            return $element->children();
        }
        $children = [];
        $namespaces = $element->getNamespaces(true);
        foreach ($namespaces as $namespace) {
            $elements = $element->children($namespace);
            foreach ($elements as $child) {
                $key = Core\DataType::info($child)->hash;
                $children[$key] = $child;
            }
        }
        $children = array_values($children);

        return $children;
    }

    /**
     * This method returns the name of the element as a string.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @return string the string
     */
    public function getElementName(\SimpleXMLElement $element)
    {
        return $element->getName();
    }

    /**
     * This method returns the namespace for the element as a string.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @return string the namespace
     *
     * @see http://php.net/manual/en/class.simplexmlelement.php
     */
    public function getElementNamespace(\SimpleXMLElement $element)
    {
        $namespaces = $element->getNamespaces();

        $namespace = (count($namespaces) > 0) ? current($namespaces) : '';

        return $namespace;
    }

    /**
     * This method returns the prefixed name of the element as a string.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @return string the prefixed name
     *
     * @see http://php.net/manual/en/class.simplexmlelement.php
     */
    public function getElementPrefixedName(\SimpleXMLElement $element)
    {
        $namespaces = $element->getNamespaces();

        $name = (count($namespaces) > 0)
            ? [current(array_keys($namespaces)), $element->getName()]
            : [$element->getName()];

        return implode(':', $name);
    }

    /**
     * This method returns the element's text content.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @return string the text content
     */
    public function getElementTextContent(\SimpleXMLElement $element)
    {
        return dom_import_simplexml($element)->textContent;
    }

    /**
     * This method returns the encoding for the character set used by the resource.
     *
     * @access public
     * @param \SimpleXMLElement $resource the resource to be evaluated
     * @return string the encoding for the character
     *                set
     */
    public function getEncoding(\SimpleXMLElement $resource)
    {
        $encoding = dom_import_simplexml($resource)->ownerDocument->encoding;
        if (!is_string($encoding)) {
            $encoding = Core\Data\Charset::UTF_8_ENCODING;
        }

        return $encoding;
    }

    /**
     * This method returns the definition of an object matching the specified id.
     *
     * @access public
     * @param string $id the object's id
     * @return Spring\Object\Definition the object's definition
     */
    public function getObjectDefinition($id)
    {
        $elements = $this->find($id);
        if (!empty($elements)) {
            $attributes = $this->getElementAttributes($elements[0]);
            $definition = [];
            foreach ($attributes as $name => $value) {
                $definition[$name] = $this->valueOf($value);
            }

            return new Spring\Object\Definition($definition);
        }

        return null;
    }

    /**
     * This method returns an object for the specified element.
     *
     * @access public
     * @param \SimpleXMLElement $element the element to be parsed
     * @return mixed the object
     * @throws Throwable\Parse\Exception indicates that a problem occurred
     *                                   when parsing
     */
    public function getObjectFromElement(\SimpleXMLElement $element)
    {
        $key = [$this->getElementName($element), $this->getElementNamespace($element)];

        if (!$this->registry->hasKey($key)) {
            throw new Throwable\Parse\Exception('Unable to parse Spring XML. Element ":name" has not been registered.', [':name' => $this->getElementPrefixedName($element)]);
        }

        $factory = $this->registry->getValue($key);

        return $factory->getObject($this, $element);
    }

    /**
     * This method returns an object for the specified element.
     *
     * @access public
     * @param string $idref the id ref of the object to get
     * @param array $idrefs a buffer to keeps track of idrefs to
     *                      help prevent circular references
     * @return mixed the object
     * @throws Throwable\Parse\Exception indicates that a problem occurred
     *                                   when parsing
     */
    public function getObjectFromIdRef($idref, $idrefs = null)
    {
        if ($idrefs !== null) {
            $this->idrefs = $idrefs;
        }
        if ($this->isId($idref)) {
            // TODO uncomment session code
            //$session_key = __CLASS__ . '::' . $this->context . '::' . $id;
            //$object = $this->session->get($session_key, null);
            $object = null;
            if ($object !== null) {
                return $object;
            } elseif ($this->singletons->hasKey($idref)) {
                return $this->singletons->getValue($idref);
            }
            $elements = $this->find($idref);
            if (!empty($elements)) {
                if (isset($this->idrefs[$idref])) { // checks for circular references
                    throw new Throwable\Parse\Exception('Unable to process Spring XML. Discovered a circular reference on id ":id".', [':id' => $idref]);
                }
                $this->idrefs[$idref] = count($this->idrefs); // stack level
                $element = $elements[0];
                $object = $this->getObjectFromElement($element);
                $attributes = $this->getElementAttributes($element);
                unset($this->idrefs[$idref]);
                if (isset($attributes['scope'])) {
                    $scope = $this->valueOf($attributes['scope']);
                    switch ($scope) {
                        // TODO uncomment session code
                        //case 'session':
                        //	$this->session->set($session_key, $object);
                        //	return $object;
                        case 'singleton':
                            $this->singletons->putEntry($idref, $object);

                            return $object;
                        case 'prototype':
                            return $object;
                        default:
                            throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "scope" token, but got ":token".', [':token' => $scope]);
                    }
                } else {
                    $this->singletons->putEntry($idref, $object);

                    return $object;
                }
            }

            return null;
        }

        throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "id" token, but got ":token".', [':token' => $idref]);
    }

    /**
     * This method returns an array of object ids that match the specified type (or if no type is specified
     * then all ids in the current context).
     *
     * @access public
     * @param string $type the type of objects
     * @return array an array object ids
     */
    public function getObjectIds($type = null)
    {
        $resource = $this->getResource();
        $resource->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
        $xpath = ($type !== null)
            ? "/spring:objects/*[@type='{$type}']/@id"
            : '/spring:objects/*/@id';
        $elements = $resource->xpath($xpath);
        $ids = [];
        foreach ($elements as $element) {
            $ids[] = $this->valueOf($element->id);
        }

        return $ids;
    }

    /**
     * This method returns the scope of the object with the specified id.
     *
     * @access public
     * @param string $id the object's id
     * @return string the scope of the the object with
     *                the specified id
     * @throws Throwable\Parse\Exception indicates that problem occurred while
     *                                   parsing
     */
    public function getObjectScope($id)
    {
        $elements = $this->find($id);
        if (!empty($elements)) {
            $attributes = $this->getElementAttributes($elements[0]);
            if (isset($attributes['scope'])) {
                $scope = $this->valueOf($attributes['scope']);
                if (!$this->isScopeType($scope)) {
                    throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "scope" token, but got ":token".', [':token' => $scope]);
                }

                return $scope;
            }

            return 'singleton';
        }

        return null;
    }

    /**
     * This method returns either the object's type for the specified id or null if the object's
     * type cannot be determined.
     *
     * @access public
     * @param string $id the object's id
     * @return string the object's type
     * @throws Throwable\InvalidArgument\Exception indicates that an argument is of the
     *                                             incorrect type
     */
    public function getObjectType($id)
    {
        $elements = $this->find($id);
        if (!empty($elements)) {
            $attributes = $this->getElementAttributes($elements[0]);
            if (isset($attributes['type'])) {
                $type = $this->valueOf($attributes['type']);
                if ($this->isClassName($type)) {
                    return str_replace('.', '\\', $type);
                }
            }
        }

        return null;
    }

    /**
     * This method returns a reference to the registry.
     *
     * @access public
     * @return Spring\Object\Registry a reference to the registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * This method returns a reference to the XML resource being parsed.
     *
     * @access public
     * @return \SimpleXMLElement a reference to the XML resource
     *                           being parsed
     */
    public function getResource()
    {
        return $this->context->getResources()->getValue(0);
    }

    /**
     * This method determines whether an object with the specified id has been defined
     * in the container.
     *
     * @access public
     * @param string $id the object's id
     * @return boolean whether an object with the specified id has
     *                 been defined in the container
     */
    public function hasObject($id)
    {
        $object = $this->find($id);

        return !empty($object);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a class
     * name.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a class name
     */
    public function isClassName($token)
    {
        return is_string($token) && preg_match('/^((\\\|_|\\.)?[a-z][a-z0-9]*)+$/i', $token);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for an id.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for an id
     */
    public function isId($token)
    {
        return is_string($token) && preg_match('/^[a-z0-9_]+$/i', $token);
    }

    /**
     * This method evaluates whether the specified string is a valid idref.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @param \SimpleXMLElement $resource the resource to query
     * @return boolean whether the specified string is a valid
     *                 idref
     * @throws Throwable\InvalidArgument\Exception indicates that an argument is incorrect
     *
     * @see http://stackoverflow.com/questions/1257867/regular-expressions-and-xpath-query
     */
    public function isIdref($token, $resource = null)
    {
        if ($resource !== null) {
            if (!$this->isId($token)) {
                throw new Throwable\InvalidArgument\Exception('Invalid argument detected (id: :id).', [':id' => $token]);
            }
            $resource->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
            $elements = $resource->xpath("/spring:objects/*[@id='{$token}' or contains(@name,'{$token}')]");
            $elements = array_filter($elements, function (\SimpleXMLElement $element) use ($token) {
                $attributes = $this->getElementAttributes($element);

                return ((isset($attributes['id']) && ($this->valueOf($attributes['id']) == $token)) || (isset($attributes['name']) && in_array($token, preg_split('/(,|;|\s)+/', $this->valueOf($attributes['name'])))));
            });

            return !empty($elements);
        }

        return $this->isId($token);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a key.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a key
     */
    public function isKey($token)
    {
        return is_string($token) && ($token != '');
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a method
     * name.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a method name
     */
    public function isMethodName($token)
    {
        return is_string($token) && preg_match('/^[a-z_][a-z0-9_]*$/i', $token);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a primitive
     * type.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a primitive type
     */
    public function isPrimitiveType($token)
    {
        return is_string($token) && in_array(strtolower($token), static::$primitives);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a property
     * name.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a property name
     */
    public function isPropertyName($token)
    {
        return is_string($token) && preg_match('/^[a-z_][a-z0-9_]*$/i', $token);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a scope
     * type.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a scope type
     */
    public function isScopeType($token)
    {
        return is_string($token) && preg_match('/^(singleton|prototype|session)$/', $token);
    }

    /**
     * This method evaluates whether the specified string matches the syntax for a space
     * preserved attribute.
     *
     * @access public
     * @param string $token the string to be evaluated
     * @return boolean whether the specified string matches the syntax
     *                 for a space preserved attribute
     */
    public function isSpacePreserved($token)
    {
        return is_string($token) && preg_match('/^preserve$/', $token);
    }

    /**
     * This method returns the first value associated with the specified object.
     *
     * @access public
     * @param mixed $value the object to be processed
     * @param string $source_encoding the source encoding
     * @param string $target_encoding the target encoding
     * @return mixed the value that was wrapped by
     *               the object
     */
    public function valueOf($value, $source_encoding = 'UTF-8', $target_encoding = 'UTF-8')
    {
        return Spring\Data\XML::valueOf($value, $source_encoding, $target_encoding);
    }

}
