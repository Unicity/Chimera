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

namespace Unicity\Spring\Object\Definition {

	use \Unicity\Throwable;

	/**
	 * This interface provides the contract for defining a SpringXML object.
	 *
	 * @access public
	 * @interface
	 * @package Spring
	 */
	interface Parser {

		/**
		 * This method parses the specified node.
		 *
		 * @access public
		 * @param \SimpleXMLElement $node                           the node to be parsed
		 * @return mixed                                            the object
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		public function parse(\SimpleXMLElement $node);

	}

}
