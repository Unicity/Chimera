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
	use \Unicity\IO;
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
		 * This variable stores a list of success handlers.
		 * @var array
		 */
		protected $handlers;

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
			$this->handlers = [];
			$this->routes = [];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->handlers);
			unset($this->routes);
		}

		/**
		 * This method adds an error handler.
		 *
		 * @access public
		 * @param callable $handler                                 the error handler to be added
		 * @return REST\Router                                      a reference to this class
		 *
		 * @see http://php.net/manual/en/function.set-error-handler.php
		 */
		public function onError(callable $handler) : REST\Router {
			set_error_handler($handler);
			return $this;
		}

		/**
		 * This method adds a route.
		 *
		 * @access public
		 * @param REST\Route $route                                 the route to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onRoute(REST\Route $route) : REST\Router {
			$this->routes[] = $route;
			return $this;
		}

		/**
		 * This method adds a shutdown handler.
		 *
		 * @access public
		 * @param callable $handler                                 the shutdown handler to be added
		 * @return REST\Router                                      a reference to this class
		 *
		 * @see http://php.net/manual/en/function.register-shutdown-function.php
		 */
		public function onShutdown(callable $handler) : REST\Router {
			register_shutdown_function($handler);
			return $this;
		}

		/**
		 * This method adds a success handler.
		 *
		 * @access public
		 * @param callable $handler                                 the success handler to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onSuccess(callable $handler) : REST\Router {
			$this->handlers[] = $handler;
			return $this;
		}

		/**
		 * This method runs the router by trying to match a route.
		 *
		 * @access public
		 * @throws Throwable\RouteNotFound\Exception                indicates that no route could be
		 *                                                          matched
		 */
		public function run() : void {
			$method = (isset($_SERVER['REQUEST_METHOD'])) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

			$uri = $_SERVER['REQUEST_URI'] ?? '';
			$query_string = $_SERVER['QUERY_STRING'] ?? '';
			$path = trim($this->substr_replace_last($query_string, '', $uri), '/? ');
			$segments = explode('/', $path);
			$segmentCt = count($segments);
			$params = [];

			$routes = $this->routes;
			$routes = array_filter($routes, function(REST\Route $route) use ($method, $segmentCt) : bool {
				return in_array($method, $route->methods) && ($segmentCt === count($route->path));
			});
			$routes = array_filter($routes, function(REST\Route $route) use ($segments, $segmentCt, &$params) : bool {
				for ($i = 0; $i < $segmentCt; $i++) {
					$segment = $route->path[$i];
					if (preg_match('/^\{.*\}$/', $segment)) {
						$regex = $route->replacements[$segment] ?? '/^.+$/';
						if (!preg_match($regex, $segments[$i])) {
							return false;
						}
						$params[$segment] = $segments[$i];
					}
					else if ($segments[$i] !== $segment) {
						return false;
					}
				}
				return true;
			});

			$message = (object) [
				'body' => new IO\InputBuffer(),
				'method' => $method,
				'path' => $path,
				'params' => $params,
			];

			$routes = array_filter($routes, function(REST\Route $route) use($message) : bool {
				foreach ($route->when as $when) {
					if (!$when($message)) {
						return false;
					}
				}
				return true;
			});

			if (!empty($routes)) {
				$pipeline = end($routes)->pipeline;
				call_user_func($pipeline, $message);
				foreach ($this->handlers as $handler) {
					$handler($message);
				}
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