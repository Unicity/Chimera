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

namespace Unicity\VS\Validation {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\VS;

	class Feedback extends Core\Object {

		/**
		 * @var Common\Mutable\HashSet
		 */
		protected $recommendations;

		/**
		 * @var Common\Mutable\HashSet
		 */
		protected $violations;

		public function __construct() {
			$this->recommendations = new Common\Mutable\HashSet();
			$this->violations = new Common\Mutable\HashSet();
		}

		public function addRecommendation(string $type, array $paths, string $message, array $values = []) : void { # TODO make multilingual
			$this->recommendations->putValue([
				'type' => strtoupper($type),
				'message' => strtr($message, ksort($values)),
				'paths' => sort($paths),
			]);
		}

		public function addRecommendations(VS\Validation\Feedback $feedback) : void {
			$this->recommendations->putValues($feedback->recommendations);
		}

		public function addViolation(string $type, array $paths, string $message, array $values = []) : void { # TODO make multilingual
			$this->violations->putValue([
				'type' => strtoupper($type),
				'message' => strtr($message, ksort($values)),
				'paths' => sort($paths),
			]);
		}

		public function addViolations(VS\Validation\Feedback $feedback) : void {
			$this->violations->putValue($feedback->violations);
		}

		public function getNumberOfRecommendations() : int {
			return $this->recommendations->count();
		}

		public function getNumberOfViolations() : int {
			return $this->violations->count();
		}

		public function toMap() : Common\IMap {
			$feedback = new ORM\JSON\Model\HashMap('\\Unicity\\VS\\Validation\\Model\\Feedback');
			$feedback->putEntries(Common\Collection::useArrays([
				'recommendation' => $this->recommendations,
				'violations' => $this->violations,
			]));
			return $feedback;
		}
	}

}