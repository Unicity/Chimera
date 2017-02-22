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

namespace Unicity\VS\Parser\Definition {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\VS;

	class ParStatement extends VS\Parser\Definition\Statement {

		protected $args;

		protected $tasks;

		public function __construct(VS\Parser\Context $context, array $args, array $tasks) {
			parent::__construct($context);
			$this->args = $args;
			$this->tasks = $tasks;
		}

		public function get() {
			$context = VS\Parser\Context::instance();
			$path = (isset($this->args[0])) ? $this->args[0]->get() : null;
			$pushed = $context->push($path);

			$policy = (isset($this->args[1])) ? $this->args[1]->get() : [];

			$successesRequired = (is_array($policy) && isset($policy['successes']))
				? Core\Convert::toInteger($policy['successes'])
				: 1;
			$failuresRequired = (is_array($policy) && isset($policy['failures']))
				? Core\Convert::toInteger($policy['failures'])
				: 1;

			$successes = 0;
			$failures = 0;

			foreach ($this->tasks as $task) {
				$status = $task->get();
				switch ($status) {
					case BT\Status::SUCCESS:
						$successes++;
						break;
					case BT\Status::FAILED:
						$failures++;
						break;
				}
			}

			if ($pushed) {
				$context->pop();
			}

			if (($successesRequired > 0) && ($successes >= $successesRequired)) {
				return BT\Status::SUCCESS;
			}

			if (($failuresRequired > 0) && ($failures >= $failuresRequired)) {
				return BT\Status::FAILED;
			}

			return BT\Status::ACTIVE;
		}

	}

}