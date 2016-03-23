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
		 * This method processes an entity.
		 *
		 * @access public
		 * @static
		 * @param BT\Task $task                                     the task to do the processing
		 * @param integer $entityId                                 the entity id being processed
		 * @param BT\Application $application                       the application running
		 * @return integer                                          the status
		 */
		public static function process(BT\Task $task, int $entityId, BT\Application $application) {
			$task->before();
			try {
				$status = $task->process($entityId, $application);
			}
			catch (\Exception $ex) {
				//$application->getLog()->add(Log\Level::WARNING, $ex->getMessage());
				$status = BT\Status::ERROR;
			}
			$task->after();
			return $status;
		}

	}

}