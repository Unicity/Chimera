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

namespace Unicity\Spring\Object\Definition;

use Unicity\Common;
use Unicity\Core;
use Unicity\Spring;
use Unicity\Throwable;

/**
 * This class manages the parsing of the Spring XML.
 *
 * @access public
 * @class
 * @package Spring
 *
 * @see https://github.com/spring-projects/spring-framework/blob/master/spring-beans/src/main/java/org/springframework/beans/factory/xml/NamespaceHandlerSupport.java
 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/xml/NamespaceHandlerSupport.html
 * @see http://sloanseaman.com/wordpress/2012/03/26/spring-custom-tags-extensible-xml-part-1/
 */
class Handler extends Core\AbstractObject
{
    /**
     * This variable stores a lookup table for object definition parsers.
     *
     * @access public
     * @var Common\Mutable\HashMap
     */
    protected $parsers;

    /**
     * This constructor initializes the class.
     *
     * @access public
     */
    public function __construct()
    {
        $this->parsers = new Common\Mutable\HashMap();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->parsers);
    }

    /**
     * This method parses the specified node.
     *
     * @access public
     * @param string $name the name to associate with the parser
     * @param \SimpleXMLElement $node the node to be parsed
     * @return mixed the object
     * @throws Throwable\Parse\Exception indicates that problem occurred while
     *                                   parsing
     */
    public function parse($name, \SimpleXMLElement $node)
    {
        return $this->parsers->getValue($name)->parse($node);
    }

    /**
     * This method registers a parser with the specified name.
     *
     * @access public
     * @param string $name the name to associate with the specified
     *                     parser
     * @param Spring\Object\Definition\Parser $parser the parser to be registered
     */
    public function registerObjectDefinitionParser($name, Spring\Object\Definition\Parser $parser)
    {
        $this->parsers->putEntry($name, $parser);
    }

}
