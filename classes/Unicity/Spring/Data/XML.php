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

namespace Unicity\Spring\Data;

use Unicity\Core;
use Unicity\Spring;

/**
 * This class represents Spring XML using dom document format.
 *
 * @access public
 * @class
 * @package Spring
 */
class XML extends Core\Data\XML
{
    /**
     * This constant represents the default namespace used by Spring XML.
     *
     * @const string
     */
    public const NAMESPACE_URI = 'http://static.unicity.com/modules/xsd/php/spring.xsd';

    /**
     * This method retypes any objects matching either the type mappings or name mappings.
     *
     * @access public
     * @static
     * @param \SimpleXMLElement $xml the Spring XML to be processed
     * @param array $mappings the type and name mappings
     */
    public static function retype(\SimpleXMLElement $xml, array $mappings)
    {
        if (!empty($mappings)) {
            $xml->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
            $objects = $xml->xpath('//spring:object');
            array_walk($objects, function (\SimpleXMLElement &$object) use ($mappings) {
                $attributes = $object->attributes();
                if (isset($attributes['type'])) {
                    $type = Spring\Data\XML::valueOf($attributes['type']);
                    if (isset($mappings['types'][$type])) {
                        $attributes['type'] = $mappings['types'][$type];
                    }
                }
                if (isset($attributes['name'])) {
                    $names = preg_split('/(,|;|\s)+/', Spring\Data\XML::valueOf($attributes['name']));
                    foreach ($names as $name) {
                        if (isset($mappings['ids'][$name])) {
                            $attributes['type'] = $mappings['ids'][$name];
                        }
                    }
                }
                if (isset($attributes['id'])) {
                    $id = Spring\Data\XML::valueOf($attributes['id']);
                    if (isset($mappings['ids'][$id])) {
                        $attributes['type'] = $mappings['ids'][$id];
                    }
                }
            });
        }
    }

}
