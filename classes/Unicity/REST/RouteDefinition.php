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

	class RouteDefinition extends Core\Object {

		protected $route;

		public function __construct(string $method, string $path, array $replacements) {
			$this->route = new REST\Route(
				array_map('trim', explode('|', strtoupper($method))),
				array_map('trim', explode('/', trim($path, '/? '))),
				$replacements
			);
		}

		public function when(callable $predicate) {
			$this->route->when[] = $predicate;
		}

		public function to(callable $pipeline) : REST\Route {
			$this->route->pipeline = $pipeline;
			return $this->route;
		}

	}

}