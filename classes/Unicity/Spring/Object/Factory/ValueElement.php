<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\Spring\Object\Factory {

	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class ValueElement extends Spring\Object\Factory {

		/**
		 * This method returns an object matching the description specified by the element.
		 *
		 * @access public
		 * @param Spring\Object\Parser $parser                      a reference to the parser
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return mixed                                            an object matching the description
		 *                                                          specified by the element
		 * @throws Throwable\Parse\Exception                        indicates that a problem occurred
		 *                                                          when parsing
		 */
		public function getObject(Spring\Object\Parser $parser, \SimpleXMLElement $element) {
			$children = $parser->getElementChildren($element, null);
			if (!empty($children)) {
				$value = '';
				foreach ($children as $child) {
					$name = $parser->getElementPrefixedName($child);
					switch ($name) {
						case 'spring:null':
							$value = $parser->getObjectFromElement($child);
							break;
						default:
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" has invalid child node ":child"', array(':tag' => 'value', ':child' => $name));
							break;
					}
				}
				if (is_string($value)) {
					return Core\Data\Charset::encode($value, $parser->getEncoding($parser->getResource()), Core\Data\Charset::UTF_8_ENCODING);
				}
				return $value;
			}
			else {
				$attributes = $element->attributes();
				$value = dom_import_simplexml($element)->textContent;
				if (isset($attributes['type'])) {
					$type = $parser->valueOf($attributes['type']);
					if (!$parser->isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					$value = Core\Convert::changeType($value, $type);
				}
				if (is_string($value)) {
					$attributes = $element->attributes('xml', true);
					if (isset($attributes['space'])) {
						$space = $parser->valueOf($attributes['space']);
						if (!$parser->isSpacePreserved($space)) {
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid space token, but got ":token".', array(':token' => $space));
						}
					}
					else {
						$value = trim($value);
					}
				}
				if (is_string($value)) {
					return Core\Data\Charset::encode($value, $parser->getEncoding($parser->getResource()), Core\Data\Charset::UTF_8_ENCODING);
				}
				return $value;
			}
		}

	}

}