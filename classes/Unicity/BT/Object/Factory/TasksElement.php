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

namespace Unicity\BT\Object\Factory {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class TasksElement extends Spring\Object\Factory {

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
			$object = new Common\Mutable\ArrayList();

			$children = $parser->getElementChildren($element, null);
			if (!empty($children)) {
				foreach ($children as $child) {
					$task = $parser->getObjectFromElement($child);

					if (!($task instanceof BT\Task)) {
						throw new Throwable\Parse\Exception('Invalid type defined. Expected a task, but got an element of type ":type" instead.', array(':type' => Core\DataType::info($object)->type));
					}

					$object->addValue($task);
				}
			}

			return $object;
		}

	}

}