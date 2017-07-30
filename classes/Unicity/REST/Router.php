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

	class Router extends Core\Object {

		protected $routes;

		public function __construct() {
			$this->routes = [];
		}

		public function route(REST\Route $route) {
			$this->routes = $route;
			return $this;
		}

		public function run() {
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
				throw new \Exception();
			}
		}

		protected function substr_replace_last($search, $replace, $string) {
			if (($position = strrpos($string, $search)) !== false) {
				$string = substr_replace($string, $replace, $position, strlen($search));
			}
			return $string;
		}

	}

}