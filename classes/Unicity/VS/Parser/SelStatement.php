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

namespace Unicity\VS\Parser {

	use \Unicity\BT;
	use \Unicity\VS;

	class SelStatement implements VS\Parser\Statement {

		protected $args;

		protected $tasks;

		public function __construct(array $args, array $tasks) {
			$this->args = $args;
			$this->tasks = $tasks;
		}

		public function get0() {
			$context = VS\Parser\Context::instance();
			$path = (isset($this->args[0])) ? $this->args[0]->get0() : null;
			$pushed = $context->push($path);

			$status = BT\Status::SUCCESS;
			foreach ($this->tasks as $task) {
				$status = $task->get0();
				if ($status !== BT\Status::FAILED) {
					break;
				}
			}

			if ($pushed) {
				$context->pop();
			}

			return $status;
		}

	}

}