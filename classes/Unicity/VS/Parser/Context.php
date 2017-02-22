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
	use \Unicity\Throwable;

	class Context extends Core\Object {

		protected $output;

		protected $stack;

		public function __construct(BT\Entity $entity) {
			$this->stack = new Common\Mutable\Stack();
			$this->stack->push([
				'entity' => $entity,
				'modules' => new Common\Mutable\HashMap(),
				'path' => '',
				'symbols' => new Common\Mutable\HashMap(),
			]);
			$this->output = new ORM\JSON\Model\ArrayList('\Unicity\MappingService\Impl\Validation\API\Master\Model\Results');
		}

		public function addModules(array $modules) {
			$context = $this->current();
			$context['modules']->putEntries($modules);
		}

		protected function current() : array {
			return $this->stack->peek();
		}

		public function getEntity() : BT\Entity {
			$context = $this->current();
			return $context['entity'];
		}

		public function getModule(string $key) {
			$stack = $this->stack->toList();
			for ($i = $stack->count() - 1; $i >= 0; $i--) {
				$context = $stack->getValue($i);
				$modules = $context['modules'];
				if ($modules->hasKey($key)) {
					return $modules->getValue($key);
				}
			}
			throw new Throwable\KeyNotFound\Exception('Unable to get element. Key ":key" does not exist.', array(':key' => $key));
		}

		public function getPath() {
			$context = $this->current();
			return $context['path'];
		}

		public function getSymbol(string $key) {
			$stack = $this->stack->toList();
			for ($i = $stack->count() - 1; $i >= 0; $i--) {
				$context = $stack->getValue($i);
				$symbols = $context['symbols'];
				if ($symbols->hasKey($key)) {
					return $symbols->getValue($key);
				}
			}
			throw new Throwable\KeyNotFound\Exception('Unable to get element. Key ":key" does not exist.', array(':key' => $key));
		}

		public function pop() : void {
			if ($this->stack->count() > 1) {
				$this->stack->pop();
			}
		}

		public function push(?string $path) : void {
			$context = $this->current();
			if (is_null($path) || in_array($path, ['.', ''])) {
				$this->stack->push([
					'entity' => $context['entity'],
					'modules' => new Common\Mutable\HashMap(),
					'path' => $context['path'],
					'symbols' => new Common\Mutable\HashMap()
				]);
			}
			else {
				$this->stack->push([
					'entity' => new BT\Entity([
						'components' => $context['entity']->getComponentAtPath($path),
						'entity_id' => $this->stack->count(),
					]),
					'modules' => new Common\Mutable\HashMap(),
					'path' => implode('.', [$context['path'], $path]),
					'symbols' => new Common\Mutable\HashMap(),
				]);
			}
		}

		public function setSymbol(string $key, $value) {
			$context = $this->current();
			if (is_null($value)) {
				$context['symbols']->removeKey($key);
			}
			else {
				$context['symbols']->putEntry($key, $value);
			}
		}

		public function output() : ORM\JSON\Model\ArrayList {
			return $this->output;
		}

	}

}