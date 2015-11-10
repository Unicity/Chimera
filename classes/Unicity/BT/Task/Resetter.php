<?php

/**
 * Copyright 2015 Unicity International
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

	/**
	 * This class represents a task resetter.
	 *
	 * @access public
	 * @class
	 * @see http://magicscrollsofcode.blogspot.com/2010/12/behavior-trees-by-example-ai-in-android.html
	 */
	class Resetter extends BT\Task\Decorator {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$status = BT\Task\Handler::process($this->task, $exchange);
			if ($status == BT\Task\Status::SUCCESS) {
				$this->task->reset();
			}
			return $status;
		}

	}

}