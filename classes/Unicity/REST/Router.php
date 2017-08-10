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

	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\HTTP;
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
		 * This variable stores a reference to the dispatcher.
		 *
		 * @access protected
		 * @var EVT\Dispatcher
		 */
		protected $dispatcher;

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
			$this->dispatcher = new EVT\Dispatcher();
			$this->routes = [];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->dispatcher);
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
		 * This method adds an exception handler.
		 *
		 * @access public
		 * @param callable $handler                                 the exception handler to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onException(callable $handler) : REST\Router {
			$this->dispatcher->subscribe('routeErrored', $handler);
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
		 * This method adds routes from a config file.
		 *
		 * @access public
		 * @param IO\File $file                                     the route config file
		 * @return REST\Router                                      a reference to this class
		 */
		public function onRouteConfiguration(IO\File $file) : REST\Router {
			$records = Config\Inc\Reader::load($file)->read();
			foreach ($records as $record) {
				$route = REST\Route::request($record['method'], $record['path'], $record['patterns'] ?? []);
				if (isset($record['when']) && is_array($record['when'])) {
					foreach ($record['when'] as $when) {
						$route->when($when);
					}
				}
				$this->onRoute($route->to($record['to']));
			}
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
			$this->dispatcher->subscribe('routeSucceeded', $handler);
			return $this;
		}

		/**
		 * This method executes the router by trying to match a route.
		 *
		 * @access public
		 * @param array $request                                    the request message (i.e. $_SERVER)
		 * @throws Throwable\RouteNotFound\Exception                indicates that no route could be
		 *                                                          matched
		 */
		public function execute(array $request = null) : void {
			if ($request === null) {
				$request = $_SERVER;
			}
			try {
				$method = (isset($request['REQUEST_METHOD'])) ? strtoupper($request['REQUEST_METHOD']) : 'GET';

				$uri = $request['REQUEST_URI'] ?? '';
				$query_string = $request['QUERY_STRING'] ?? '';
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
						else {
							if ($segments[$i] !== $segment) {
								return false;
							}
						}
					}
					return true;
				});

				$message = HTTP\RequestMessage::factory([
					'body' => new IO\InputBuffer(),
					'method' => $method,
					'path' => $path,
					'params' => $params,
					'uri' => $uri,
				]);

				$routes = array_filter($routes, function(REST\Route $route) use ($message) : bool {
					foreach ($route->when as $when) {
						if (!$when($message)) {
							return false;
						}
					}
					return true;
				});

				if (!empty($routes)) {
					$pipeline = end($routes)->pipeline;
					call_user_func_array($pipeline, [$message, $this->dispatcher]);
					$this->dispatcher->publish('routeSucceeded', $message);
				}
				else {
					throw new Throwable\RouteNotFound\Exception('Unable to route message.');
				}
			}
			catch (\Throwable $throwable) {
				$this->dispatcher->publish('routeErrored', $throwable);
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