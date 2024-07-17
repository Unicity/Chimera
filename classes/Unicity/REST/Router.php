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
	use \Unicity\Log;

	class Router extends Core\AbstractObject {

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
		 * @var EVT\Server
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
		 *
		 * @see http://php.net/manual/en/function.set-error-handler.php
		 */
		public function __construct() {
			$dispatcher = new EVT\Server();

			$this->dispatcher = $dispatcher;
			$this->routes = [];

			set_error_handler(function(int $code, string $message, string $file, int $line) use ($dispatcher) {
				$dispatcher->publish('routeErrored', new \ErrorException($message, $code, 1, $file, $line));
			});
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
		 * This method adds routes from a config file.
		 *
		 * @access public
		 * @param IO\File $file                                     the route config file
		 * @return REST\Router                                      a reference to this class
		 */
		public function onConfiguration(IO\File $file) : REST\Router {
			$entries = Config\Inc\Reader::load($file)->read();
			foreach ($entries as $entry) {
				$route = REST\Route::request($entry['method'], $entry['path'], $entry['patterns'] ?? []);
				if (isset($entry['when']) && is_array($entry['when'])) {
					foreach ($entry['when'] as $when) {
						$route->when($when);
					}
				}
				if (isset($entry['with']) && is_array($entry['with'])) {
					$route->with($entry['with']);
				}
				$this->onRoute($route->to($entry['to']));
			}
			return $this;
		}

		/**
		 * This method adds a operating handler.
		 *
		 * @access public
		 * @param callable $routes                                  the operating handler to be added
		 * @return void										                          a reference to this class
		 */
		public function onRoutesFound($routes): void {
			\Unicity\Log\Manager::instance()->add(\Unicity\Log\Level::informational(), __METHOD__, ['routes' => $routes]);
		}

		/**
		 * This method adds an exception handler.
		 *
		 * @access public
		 * @param callable $handler                                 the exception handler to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onException(callable $handler) : REST\Router {
			$this->dispatcher->subscribe('routeException', $handler);
			return $this;
		}

		/**
		 * This method adds an error handler.
		 *
		 * @access public
		 * @param callable $handler                                 the error handler to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onError(callable $handler) : REST\Router {
			$this->dispatcher->subscribe('routeErrored', $handler);
			return $this;
		}

		/**
		 * This method adds a operating handler.
		 *
		 * @access public
		 * @param callable $handler                                 the operating handler to be added
		 * @return REST\Router                                      a reference to this class
		 */
		public function onOperation(callable $handler) : REST\Router {
			$this->dispatcher->subscribe('routeOperating', $handler);
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
		 * This method executes the router by trying to match a route.
		 *
		 * @access public
		 * @param array $inquiry                                    the inquiry message (i.e. $_SERVER)
		 */
		public function execute(array $inquiry = null) : void {
			if ($inquiry === null) {
				$inquiry = $_SERVER;
			}
			try {
				$method = (isset($inquiry['REQUEST_METHOD'])) ? strtoupper($inquiry['REQUEST_METHOD']) : 'GET';

				$uri = $inquiry['REQUEST_URI'] ?? '';
				$query_string = $inquiry['QUERY_STRING'] ?? '';
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
							$regex = $route->patterns[$segment] ?? '/^.+$/';
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

				$args = [];
				foreach ($params as $key => $val) {
					$args[trim($key, ' {}')] = $val;
				}
				$params = $args;
				unset($args);

				if (!empty($_FILES)) {
					$buffer = [];
					foreach ($_FILES as $key => $meta) {
						$buffer[$key] = (new IO\FileInputBuffer($key))->getBytes();
					}
					$request = HTTP\Request::factory([
						'body' => new IO\StringRef(json_encode($buffer)),
						'method' => $method,
						'path' => $path,
						'params' => $params,
						'uri' => $uri,
					]);
					unset($buffer);
				}
				else {
					$request = HTTP\Request::factory([
						'body' => new IO\InputBuffer(),
						'method' => $method,
						'path' => $path,
						'params' => $params,
						'uri' => $uri,
					]);
				}

				// Function that determines which routes meet the conditions of the request
				$routes = array_filter($routes, function(REST\Route $route) use ($request) : bool {
					foreach ($route->when as $when) {
						if (!$when($request)) {
							return false;
						}
					}
					return true;
				});

				if (empty($routes)) {
					throw new Throwable\RouteNotFound\Exception('Unable to route message.');
				}

				// $route = end($routes);
				$this->onRoutesFound($routes);
				$route = $this->selectPaymentMethod($request, $routes);
				
				$pipeline = $route->pipeline;
				$pipeline($request, $route->arguments); 
				try {
					$this->dispatcher->publish('routeOperating', $request);
				}
				catch (\Throwable $error) {
					$this->dispatcher->publish('routeException', $error);
				}
			}
			catch (\Throwable $failure) {
				$this->dispatcher->publish('routeErrored', $failure);
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

		/**
		 * This method selects the appropriate route from among a set of routes
		 *
		 * @access public
		 * @param EVT\Request $request															payment providers list
		 * @param REST\Router[] $routes															array of routes
		 * @return REST\Router                                    	array of providers
		 */
		protected function selectPaymentMethod(EVT\Request $request, array $routes) {

			if (count($routes) === 1) {
				return end($routes);
			}
			
			try {
			
				// ... Proceed to select based on probability ...
				
				// Check if routes belong to one or different providers
				// $paymentProviders = getPaymentProvidersList() // for future, get payment providers list from DB/Static file/ etc
				$paymentProviders = ['NetworkMerchant', 'Worldpay']; // ... Paypal, etc...
				$foundProviders = $this->getProvidersFromFoundRoutes($paymentProviders, $routes);
				
				// Percent configuration 
				// $configuration = getConfigurationFromDB(); // for future, get configuration from DB/Static file/etc
				$configuration = [
					'US' => [
						'NetworkMerchant' => ['v1' => 0.9, 'v2' => 0.1], // each key in this array must match the name of the file it refers to: NetworkMerchants.v1.php/NetworkMerchants.v2.php
						// 'Worldpay' => ['v1' => 0.9, 'v2' => 0.1],
						'NetworkMerchantWorldpay' => ['NetworkMerchant' => 0.2, 'Worldpay' => 0.8],
						'NetworkMerchantPaypalWorldpay' => ['NetworkMerchant' => 0.7, 'Paypal' => 0.1, 'Worldpay' => 0.2],
					]
				];

				$providerKey = implode('', $foundProviders);
				// One Provider -> we must apply the probabilistic logic with configuration (processor IDs)
				// i.e.: 90% v1 / 10% v2 -> current implementation for Nuvei 10/60 rule
				// More than one provider -> we must apply the probabilistic logic between providers/markets
				// i.e.: US/PR 80% Worldpay / 20% NetworkMerchant
				if (isset($configuration[$request->params['market']]) && isset($configuration[$request->params['market']][$providerKey])) {
					$route = $this->selectRoute($routes, $configuration[$request->params['market']][$providerKey]);
				} else {
					// No configuration for the specified routes, return last route
					$route = end($routes);
				}

				return $route;
				
			} catch (\Throwable $th) {
				Log\Manager::instance()->add(\Unicity\Log\Level::error(), __METHOD__, ['routes' => $routes, 'error' => $th->getMessage()]);
			}

			return end($routes);

		}

		/**
		 * This method returns the list of providers found in an array of Routes.
		 *
		 * @access public
		 * @param array $paymentProviders  payment providers list
		 * @param REST\Router[] $routes    array of routes
		 * @return array                   array of providers
		 */
		protected function getProvidersFromFoundRoutes(array $paymentProviders, array $routes) {
			$providerMap = [];
			$keys = array_keys($routes);
			foreach ($paymentProviders as $provider) {
        $providerMap[$provider] = false;
    	}

			foreach ($paymentProviders as $provider) {
				foreach ($keys as $key) {
					$route = $routes[$key];
					$config = $route->arguments['config'];
					if (strpos($config, $provider) !== false) {
						$providerMap[$provider] = true;
					}
				}
			}
			
			$providers = array_keys(array_filter($providerMap));
			sort($providers);
			return $providers;
		}

		/**
		 * This method returns the route found among an array of routes based on probability.
		 *
		 * @access public
		 * @param REST\Router[] $routes															  array of routes
		 * @param array $probabilities															  array of probabilities
		 * @return REST\Router                                    		route found
		 */
		protected function selectRoute($routes, $probabilities): REST\Router {
			$random = mt_rand() / mt_getrandmax();
			$cumulativeProbability = 0.0;
			$keys = array_keys($routes);
			foreach ($probabilities as $version => $probability) {
				$cumulativeProbability += $probability;
				if ($random <= $cumulativeProbability) {
					foreach ($keys as $key) {
						$route = $routes[$key];
						$config = $route->arguments['config'];
						if (strpos($config, $version) !== false) {
							return $route;
						}
					}
				}
			}
	
			return end($routes);
		}

	}

}