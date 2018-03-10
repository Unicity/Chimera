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

	class RouteDefinition extends Core\AbstractObject {

		/**
		 * This variable stores the route's definition.
		 *
		 * @access protected
		 * @var REST\Route
		 */
		protected $route;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param string $method                                    the method(s) to be routed
		 * @param string $path                                      the path to be routed
		 * @param array $patterns                                   the patterns for evaluating path
		 *                                                          segments
		 */
		public function __construct(string $method, string $path, array $patterns) {
			$this->route = new REST\Route(
				array_map('trim', explode('|', strtoupper($method))),
				array_map('trim', explode('/', trim($path, '/? '))),
				$patterns
			);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->route);
		}

		/**
		 * This method sets a predicate to be used in the route evaluation process.
		 *
		 * @access public
		 * @param callable $predicate                               the predicate to be set
		 * @return REST\RouteDefinition                             a reference to this class
		 */
		public function when(callable $predicate) : REST\RouteDefinition {
			$this->route->when[] = $predicate;
			return $this;
		}

		/**
		 * This method sets any internal arguments.
		 *
		 * @access public
		 * @param array $arguments                                  the internal arguments to be set
		 * @return REST\RouteDefinition                             a reference to this class
		 */
		public function with(array $arguments) : REST\RouteDefinition {
			$this->route->arguments = REST\Arguments::put($this->route->arguments, $arguments);
			return $this;
		}

		/**
		 * This method sets the route's pipeline.
		 *
		 * @access public
		 * @param callable $pipeline                                the pipeline to be set
		 * @return REST\Route                                       the route
		 */
		public function to(callable $pipeline) : REST\Route {
			$this->route->pipeline = $pipeline;
			return $this->route;
		}

	}

}