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
	final class Manager extends Core\AbstractObject {

		/**
		 * This variable stores an singleton instance of this class.
		 *
		 * @access private
		 * @static
		 * @var EVT\Server\Manager
		 */
		private static $singleton = null;

		/**
		 * This variable stores a list of servers.
		 *
		 * @access private
		 * @var array
		 */
		private $servers;

		/**
		 * This constructor initializes the class.
		 *
		 * @access private
		 */
		private function __construct() {
			$this->servers = [];
			$this->servers['default'] = new EVT\Server('default');
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->servers);
		}

		/**
		 * This method returns the server matching the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of server
		 * @return EVT\Server                                       the server matching the specified name
		 */
		public function get(string $name = 'default') : EVT\Server {
			return $this->servers[$name];
		}

		/**
		 * This method returns whether there is a server matching the specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of server
		 * @return bool                                             whether there is a server matching
		 *                                                          the specified name
		 */
		public function has(string $name) : bool {
			return isset($this->servers[$name]);
		}

		/**
		 * This method returns the specified server.
		 *
		 * @access public
		 * @param string $name                                      the name of server
		 */
		public function remove(string $name) {
			if (($name !== '') && isset($this->servers[$name])) {
				unset($this->servers[$name]);
			}
		}

		/**
		 * This method create a server of the specified type and assigns it a name.
		 *
		 * @access public
		 * @param string $name                                      the name of server
		 * @param string $type                                      the type of server to create
		 */
		public function set(string $name, string $type) {
			if ($name !== 'default') {
				$server =  new $type($name);
				if ($server instanceof EVT\IServer) {
					$this->servers[$name] = $server;
				}
			}
		}

		/**
		 * This method returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @return EVT\Server\Manager                                   a singleton instance of this
		 *                                                              class
		 */
		public static function instance() : EVT\Server\Manager {
			if (static::$singleton === null) {
				static::$singleton = new EVT\Server\Manager();
			}
			return static::$singleton;
		}

	}

}