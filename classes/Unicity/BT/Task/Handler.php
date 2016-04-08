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
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public static function process(BT\Task $task, BT\Engine $engine, string $entityId) {
			$task->before();
			try {
				$status = $task->process($engine, $entityId);
			}
			catch (\Exception $ex) {
				//$engine->getErrorLog()->add(Log\Level::WARNING, $ex->getMessage());
				//var_dump($ex->getMessage()); exit();
				$status = BT\Status::ERROR;
			}
			$task->after();
			return $status;
		}

	}

}