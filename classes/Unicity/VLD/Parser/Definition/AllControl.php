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

namespace Unicity\VLD\Parser\Definition {

	use \Unicity\Core;
	use \Unicity\VLD;

	class AllControl extends VLD\Parser\Definition\Control {

		protected $policy;

		protected $statements;

		public function __construct(VLD\Parser\Context $context, $policy, array $statements) {
			parent::__construct($context);
			$this->policy = $policy;
			$this->statements = $statements;
		}

		public function get() {
			if (is_array($this->policy) && isset($this->policy['successes'])) {
				$successesRequired = Core\Convert::toInteger($this->policy['successes']);
				if ($successesRequired < 0) {
					$successesRequired = count($this->statements) + $successesRequired;
				}
			}
			else {
				$successesRequired = count($this->statements);
			}

			$feedback = new VLD\Parser\Feedback($this->context->getPath());

			$results = array();
			$successes = 0;

			foreach ($this->statements as $i => $statement) {
				$results[$i] = $statement->get();
				$feedback->addRecommendations($results[$i]);
				if ($results[$i]->getNumberOfViolations() === 0) {
                    $successes++;
                }
			}

			if ($successes < $successesRequired) {
				foreach ($results as $result) {
					$feedback->addViolations($result);
				}
			}

			return $feedback;
		}

	}

}