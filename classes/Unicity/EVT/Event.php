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

	use \Unicity\Core;

	abstract class Event extends Core\Object { // subclass using "past" tense

		protected $target;
		protected $timestamp;

		public function __construct($target) {
			$this->target = $target;
			$this->timestamp = self::timestamp();
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->target);
			unset($this->timestamp);
		}

		public function getTarget() {
			return $this->target;
		}

		public function getTimestamp() {
			return $this->timestamp;
		}

		public function jsonSerialize() {
			return [
				'target' => $this->target,
				'timestamp' => $this->timestamp,
			];
		}

		protected static function timestamp() {
			$t = microtime(true);
			$micro = sprintf('%06d', ($t - floor($t)) * 1000000);
			return date('Y-m-d H:i:s.' . $micro, $t);
		}

	}

}