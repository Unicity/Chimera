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

namespace Unicity\ORM\Dynamic\Model {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	/**
	 * This class represents an array list.
	 *
	 * @access public
	 * @class
	 * @package ORM
	 */
	class ArrayList extends Common\Mutable\ArrayList implements ORM\IModel {

		/**
		 * This variable stores whether field names are case sensitive.
		 *
		 * @access protected
		 * @var boolean
		 */
		protected $case_sensitive;

		/**
		 * This method initializes the class with the specified values (if any are provided).
		 *
		 * @access public
		 * @param \Traversable $elements                            a traversable array or collection
		 * @param boolean $case_sensitive                           whether field names are case
		 *                                                          sensitive
		 */
		public function __construct($elements = null, $case_sensitive = true) {
			$this->case_sensitive = Core\Convert::toBoolean($case_sensitive);
			parent::__construct($elements);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->case_sensitive);
		}

	}

}