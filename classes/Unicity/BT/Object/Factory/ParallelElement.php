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

declare(strict_types = 1);

namespace Unicity\BT\Object\Factory {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class ParallelElement extends Spring\Object\Factory {

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

			$type = (isset($attributes['type']))
				? $parser->valueOf($attributes['type'])
				: '\\Unicity\\BT\\Task\\Parallel';

			$element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
			$children = $element->xpath('./spring-bt:blackboard');
			$blackboard = (!empty($children))
				? $parser->getObjectFromElement($children[0])
				: null;

			$element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
			$children = $element->xpath('./spring-bt:policy');
			$policy = (!empty($children))
				? $parser->getObjectFromElement($children[0])
				: null;

			$object = new $type($blackboard, $policy);

			if (!($object instanceof BT\Task\Parallel)) {
				throw new Throwable\Parse\Exception('Invalid type defined. Expected a task parallel, but got an element of type ":type" instead.', array(':type' => $type));
			}

			if (isset($attributes['title'])) {
				$object->setTitle($parser->valueOf($attributes['title']));
			}

			$element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
			$children = $element->xpath('./spring-bt:tasks');
			if (!empty($children)) {
				foreach ($children as $child) {
					$object->addTasks($parser->getObjectFromElement($child));
				}
			}

			return $object;
		}

	}

}