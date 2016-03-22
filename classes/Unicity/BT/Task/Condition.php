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

	/**
	 * This class represents a task condition.
	 *
	 * @access public
	 * @class
	 * @see https://docs.unrealengine.com/latest/INT/Engine/AI/BehaviorTrees/HowUE4BehaviorTreesDiffer/index.html
	 * @see https://sourcemaking.com/refactoring/replace-nested-conditional-with-guard-clauses
	 * @see http://www.tutisani.com/software-architecture/nested-if-vs-guard-condition.html
	 */
	final class Condition extends BT\Task\Composite {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$state = BT\Task\Handler::process($this->tasks->getValue(0), $entity);
			if ($state instanceof BT\State\Success) {
				return BT\Task\Handler::process($this->tasks->getValue(1), $entity);
			}
			return $state;
		}

	}

}