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

namespace Unicity\Spring\Object\Factory {

	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class EntryElement extends Spring\Object\Factory {

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
			$object = array();
			$attributes = $parser->getElementAttributes($element);

			if (!isset($attributes['key'])) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => $parser->getElementPrefixedName($element), ':attribute' => 'key'));
			}
			$key = $parser->valueOf($attributes['key']);
			if (!$parser->isKey($key)) {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid entry key, but got ":key".', array(':key' => $key));
			}

			$children = $parser->getElementChildren($element, null);
			if (!empty($children)) {
				foreach ($children as $child) {
					$object[$key] = $parser->getObjectFromElement($child);
				}
			}
			else if (isset($attributes['value-ref'])) {
				$object[$key] = $parser->getObjectFromIdRef($parser->valueOf($attributes['value-ref']));
			}
			else if (isset($attributes['value'])) {
				$value = $parser->valueOf($attributes['value']);
				if (isset($attributes['value-type'])) {
					$type = $parser->valueOf($attributes['value-type']);
					if (!$parser->isPrimitiveType($type)) {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', array(':type' => $type));
					}
					$value = Core\Convert::changeType($value, $type);
				}
				$object[$key] = $value;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'entry', ':attribute' => 'value'));
			}

			return $object;
		}

	}

}