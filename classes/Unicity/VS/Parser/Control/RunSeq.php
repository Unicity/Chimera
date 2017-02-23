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

namespace Unicity\VS\Parser\Control {

	use \Unicity\VS;

	class RunSeq extends VS\Parser\Control {

		protected $policy;

		protected $statements;

		public function __construct(VS\Parser\Context $context, $policy, array $statements) {
			parent::__construct($context);
			$this->policy = $policy;
			$this->statements = $statements;
		}

		public function get() {
			$feedback = new VS\Validation\Feedback();

			foreach ($this->statements as $statement) {
				$result = $statement->get();

				$feedback->addRecommendations($result);
				if ($result->getNumberOfViolations() > 0) {
					$feedback->addViolations($result);
					break;
				}
			}

			return $feedback;
		}

	}

}