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
	use \Unicity\EVT;

	class EventBroker extends Core\Object {

		protected $events;

		protected $commands;

		protected $queries;

		public function __construct() {
			$this->events = array();
			$this->commands = array();
			$this->queries = array();
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->events);
			unset($this->commands);
			unset($this->queries);
		}

		public function addCommandListener(callable $listener) {
			$this->commands[] = $listener;
		}

		public function addQueryListener(callable $listener) {
			$this->queries[] = $listener;
		}

		public function executeCommand(EVT\Command $command) {
			foreach ($this->commands as $listener) {
				$listener($this, $command);
			}
		}

		public function executeQuery(EVT\Query $query) {
			foreach ($this->queries as $listener) {
				$listener($this, $query);
			}
		}

	}

}