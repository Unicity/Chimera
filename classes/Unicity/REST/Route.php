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

		public $methods;
		public $path;
		public $pipeline;
		public $replacements;
		public $when;

		public function __construct(array $methods, array $path, array $replacements) {
			$this->methods = $methods;
			$this->path = $path;
			$this->pipeline = null;
			$this->replacements = $replacements;
			$this->when = [];
		}

		public static function request(string $method, string $path, array $replacements = []) : REST\RouteDefinition {
			return new REST\RouteDefinition($method, $path, $replacements);
		}

	}

}