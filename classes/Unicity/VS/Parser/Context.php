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

namespace Unicity\VS\Parser {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\ORM;
	use \Unicity\VS;

	class Context extends Core\Object {

		protected $results;

		protected $stack;

		public function __construct(BT\Entity $entity) {
			$this->results = new ORM\JSON\Model\ArrayList('\Unicity\MappingService\Impl\Validation\API\Master\Model\Results');
			$this->stack = new Common\Mutable\Stack();
			$this->stack->push($entity);
		}

		public function current() : BT\Entity {
			return $this->stack->peek();
		}

		public function pop() : bool {
			if ($this->stack->count() > 1) {
				$this->stack->pop();
				return true;
			}
			return false;
		}

		public function push(string $path) {
			$this->stack->push($entity = new BT\Entity([
				'components' => $this->current()->getComponentAtPath($path),
				'entity_id' => $this->stack->count(),
			]));
		}

		public function results() {
			return $this->results;
		}

		public function root() : BT\Entity {
			return $this->stack->toList()->getValue(0);
		}

		protected static $singleton = null;

		public static function instance(BT\Entity $entity = null) : VS\Parser\Context {
			if (static::$singleton === null) {
				static::$singleton = new VS\Parser\Context($entity);
			}
			return static::$singleton;
		}

	}

}