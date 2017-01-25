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

	use \Unicity\AOP;
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
			$args = func_get_args();
			array_shift($args);

			return AOP\Advice::factory(new AOP\JoinPoint($args, ['class' => $task->__getClass(), 'method' => 'process']))
				->register($task)
				->execute([$task, 'process']);
		}

	}

}