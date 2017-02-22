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

namespace Unicity\VS\Parser\Task {

	use \Unicity\BT;
	use \Unicity\VS;

	class SeqControl extends VS\Parser\Task {

		protected $policy;

		protected $statements;

		public function __construct(VS\Parser\Context $context, $policy, array $statements) {
			parent::__construct($context);
			$this->policy = $policy;
			$this->statements = $statements;
		}

		public function get() {
			$status = BT\Status::SUCCESS;
			foreach ($this->statements as $statement) {
				$status = $statement->get();
				if ($status !== BT\Status::SUCCESS) {
					break;
				}
			}
			return $status;
		}

	}

}