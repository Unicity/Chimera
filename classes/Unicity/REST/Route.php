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

	class Route extends Core\Object {

		/**
		 * This variable stores any internal arguments.
		 *
		 * @access public
		 * @var array
		 */
		public $internals;

		/**
		 * This variable stores the HTTP methods.
		 *
		 * @access public
		 * @var array
		 */
		public $methods;

		/**
		 * This variable stores the path segments.
		 *
		 * @access public
		 * @var array
		 */
		public $path;

		/**
		 * This variable stores the pipeline.
		 *
		 * @access public
		 * @var callable
		 */
		public $pipeline;

		/**
		 * This variable stores the patterns for evaluating path segments.
		 *
		 * @access public
		 * @var array
		 */
		public $patterns;

		/**
		 * This variable stores the "when" predicates.
		 *
		 * @access public
		 * @var array
		 */
		public $when;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param array $methods                                    the methods to be routed
		 * @param array $path                                       the path segments to be routed
		 * @param array $patterns                                   the patterns for evaluating path
		 *                                                          segments
		 */
		public function __construct(array $methods, array $path, array $patterns) {
			$this->methods = $methods;
			$this->path = $path;
			$this->pipeline = null;
			$this->patterns = $patterns;
			$this->internals = [];
			$this->when = [];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->methods);
			unset($this->path);
			unset($this->pipeline);
			unset($this->patterns);
			unset($this->internals);
			unset($this->when);
		}

		/**
		 * This method returns a new route definition.
		 *
		 * @access public
		 * @static
		 * @param string $method                                    the method(s) to be routed
		 * @param string $path                                      the path to be routed
		 * @param array $patterns                                   the patterns for evaluating path
		 *                                                          segments
		 * @return REST\RouteDefinition                             the new route definition
		 */
		public static function request(string $method, string $path, array $patterns = []) : REST\RouteDefinition {
			return new REST\RouteDefinition($method, $path, $patterns);
		}

	}

}