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

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Core;

	/**
	 * This class represents a process handler.
	 *
	 * @access public
	 * @class
	 */
	class Handler extends Core\Object {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @static
		 * @param BT\Task $task                                     the task to do the processing
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public static function process(BT\Task $task, BT\Entity $entity) {
			$task->before();
			try {
				$state = $task->process($entity);
			}
			catch (\Exception $ex) {
				$state = BT\State\Error::with($entity);
			}
			$task->after();
			return $state;
		}

	}

}