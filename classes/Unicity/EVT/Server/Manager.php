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

namespace Unicity\EVT\Server {

	use \Unicity\Core;
	use \Unicity\EVT;

	/**
	 * This class manages a pool of dispatchers.
	 *
	 * @access public
	 * @class
	 * @package EVT
	 */
	final class Manager extends Core\Object {

		private static $singleton = null;

		private $servers;

		private function __construct() {
			$this->servers = [];
			$this->servers['default'] = new EVT\Server('default');
		}

		public function get(string $name) : EVT\Server {
			return $this->servers[$name];
		}

		public function has(string $name) : bool {
			return isset($this->servers[$name]);
		}

		public function remove(string $name) {
			if (($name !== '') && isset($this->servers[$name])) {
				unset($this->servers[$name]);
			}
		}

		public function set(string $name, string $type) {
			if ($name !== 'default') {
				$dispatcher =  new $type($name);
				if ($dispatcher instanceof EVT\Server) {
					$this->servers[$name] = $dispatcher;
				}
			}
		}

		public static function instance() : EVT\Server\Manager {
			if (static::$singleton === null) {
				static::$singleton = new EVT\Server\Manager();
			}
			return static::$singleton;
		}

	}

}