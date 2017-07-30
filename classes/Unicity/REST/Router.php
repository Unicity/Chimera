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

namespace Unicity\REST {

	use \Unicity\Core;
	use \Unicity\REST;
	use \Unicity\Throwable;

	class Router extends Core\Object {

		/**
		 * This variable stores a singleton instance of this class.
		 *
		 * @access protected
		 * @var REST\Router
		 */
		protected static $singleton = null;

		/**
		 * This variable stores the routes.
		 *
		 * @access protected
		 * @var array
		 */
		protected $routes;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->routes = [];
		}

		/**
		 * This method adds a route.
		 *
		 * @access public
		 * @param REST\Route $route                                 the route to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function route(REST\Route $route) : REST\Router {
			$this->routes = $route;
			return $this;
		}

		public function run() : void {
			$method = (isset($_SERVER['REQUEST_METHOD'])) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

			$uri = $_SERVER['REQUEST_URI'] ?? '';
			$query_string = $_SERVER['QUERY_STRING'] ?? '';
			$path = explode('/', trim($this->substr_replace_last($query_string, '', $uri), '/? '));
			$pathCt = count($path);

			$routes = $this->routes;
			$routes = array_filter($routes, function(REST\Route $route) use ($method, $pathCt) : bool {
				return in_array($method, $route->methods) && ($pathCt === count($route->path));
			});
			$routes = array_filter($routes, function(REST\Route $route) use ($path, $pathCt) : bool {
				for ($i = 0; $i < $pathCt; $i++) {
					$seqment = $route->path[$i];
					if (preg_match('/^\{.*\}$/', $seqment)) {
						$regex = $route->replacements[$seqment] ?? '/^.+$/';
						if (!preg_match($regex, $path[$i])) {
							return false;
						}
					}
					else if ($path[$i] !== $seqment) {
						return false;
					}
				}
				return true;
			});
			$routes = array_filter($routes, function(REST\Route $route) : bool {
				foreach ($route->when as $when) {
					if (!$when()) {
						return false;
					}
				}
				return true;
			});

			if (!empty($routes)) {
				$pipeline = end($routes)->pipeline;
				$pipeline();
			}
			else {
				throw new Throwable\RouteNotFound\Exception();
			}
		}

		/**
		 * This method replaces the last occurrence in the string.
		 *
		 * @access protected
		 * @param string $search                                    the substring to be searched for
		 * @param string $replace                                   the replacement string
		 * @param string $subject                                   the subject string
		 * @return string                                           the result string
		 */
		protected function substr_replace_last(string $search, string $replace, string $subject) : string {
			if (($position = strrpos($subject, $search)) !== false) {
				$subject = substr_replace($subject, $replace, $position, strlen($search));
			}
			return $subject;
		}

		/**
		 * This method returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @return REST\Router                                      the singleton instance of this class
		 */
		public static function instance() : REST\Router {
			if (static::$singleton === null) {
				static::$singleton = new REST\Router();
			}
			return static::$singleton;
		}

	}

}