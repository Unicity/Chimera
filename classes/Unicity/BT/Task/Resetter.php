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

	/**
	 * This class represents a task resetter.
	 *
	 * @access public
	 * @class
	 * @see http://magicscrollsofcode.blogspot.com/2010/12/behavior-trees-by-example-ai-in-android.html
	 */
	class Resetter extends BT\Task\Decorator {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$status = BT\Task\Handler::process($this->task, $engine, $entityId);
			if ($status == BT\Status::SUCCESS) {
				$this->task->reset($engine);
			}
			return $status;
		}

	}

}