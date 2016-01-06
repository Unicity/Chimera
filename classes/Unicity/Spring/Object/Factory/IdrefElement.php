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

	use \Unicity\Spring;
	use \Unicity\Throwable;

	class IdrefElement extends Spring\Object\Factory {

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
			$attributes = $element->attributes();

			if (isset($attributes['local'])) {
				$object = $parser->valueOf($attributes['local']);
				if (!$parser->isIdref($object, $parser->getResource())) {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid "idref" token, but got ":token".', array(':token' => $object));
				}
				return $object;
			}
			else if (isset($attributes['object'])) {
				$object = $parser->valueOf($attributes['object']);
				return $object;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'idref', ':attribute' => 'object'));
			}
		}

	}

}