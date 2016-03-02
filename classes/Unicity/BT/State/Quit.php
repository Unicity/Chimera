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

namespace Unicity\BT\State {

	use \Unicity\BT;

	/**
	 * This class represents a task state that did not fail or error, but just needs to quit.
	 *
	 * @access public
	 * @class
	 */
	class Quit extends BT\State {

		/**
		 * This method returns a new instance of this class.
		 *
		 * @access public
		 * @static
		 * @param BT\Entity $entity                                 the entity
		 * @return BT\State                                         the state
		 */
		public static function with(BT\Entity $entity) {
			return new BT\State\Quit(BT\Status::QUIT, $entity);
		}

	}

}