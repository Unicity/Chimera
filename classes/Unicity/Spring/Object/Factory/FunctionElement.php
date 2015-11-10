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

	use \Unicity\Spring;
	use \Unicity\Throwable;

	class FunctionElement extends Spring\Object\Factory {

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

			if (isset($attributes['delegate-object']) && isset($attributes['delegate-method'])) {
				$delegate_object = $parser->valueOf($attributes['delegate-object']);
				if ($parser->isClassName($delegate_object) && class_exists($delegate_object)) {
					$delegate_method = $parser->valueOf($attributes['delegate-method']);
					if ($parser->isMethodName($delegate_method) && method_exists($delegate_object, $delegate_method)) {
						return array($delegate_object, $delegate_method);
					}
					else {
						throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid method name, but got ":name".', array(':name' => $delegate_method));
					}
				}
				else {
					throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid class name, but got ":name".', array(':name' => $delegate_object));
				}
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing a valid class name and/or method name.', array(':tag' => 'function'));
			}
		}

	}

}