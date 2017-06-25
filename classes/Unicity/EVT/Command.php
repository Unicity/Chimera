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

namespace Unicity\EVT {

	use \Unicity\EVT;

	class Command extends EVT\EventArgs { // subclass using "present" tense

		protected $value;

		public function __construct($target, $value = null) {
			parent::__construct($target);
			$this->value = $value;
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->value);
		}

		public function getValue() {
			return $this->value;
		}

		public function jsonSerialize() {
			return [
				'target' => $this->target,
				'value' => $this->value,
			];
		}

	}

}