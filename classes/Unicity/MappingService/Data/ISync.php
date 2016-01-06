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

namespace Unicity\MappingService\Data {

	use \Unicity\Common;
	use Unicity\MappingService;

	/**
	 * This interface provides a contract that defines a model sync.
	 *
	 * @access public
	 * @interface
	 * @package MappingService
	 */
	interface ISync {

		/**
		 * This method returns a new model with the sync differences.
		 *
		 * @access public
		 * @static
		 * @param \Unicity\MappingService\Data\Model $theirs        the model going to be synced
		 * @param \Unicity\MappingService\Data\Model $ours          the model representing the current
		 *                                                          record in the database
		 * @param \Unicity\Common\IMap $commits                     the last commits made by humans
		 * @return \Unicity\MappingService\Data\ISync               a model representing the differences
		 */
		public static function differences(MappingService\Data\Model $theirs, MappingService\Data\Model $ours, Common\IMap $commits = null);

	}

}