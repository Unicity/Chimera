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

namespace Unicity\VLD\Parser {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;
	use \Unicity\VLD;

	class Context extends Core\Object {

		/**
		 * This variable stores the stack for maintaining context switches.
		 *
		 * @access protected
		 * @var Common\Mutable\Stack
		 */
		protected $stack;

		/**
		 * This constructor initializes the class with the specified entity (which represents
		 * the initial entity being validated).
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the initial entity to be
		 *                                                          validated
		 */
		public function __construct(BT\Entity $entity) {
			$this->stack = new Common\Mutable\Stack();
			$this->stack->push([
				'entity' => $entity,
				'modules' => new Common\Mutable\HashMap(),
				'path' => '',
				'symbols' => new Common\Mutable\HashMap(),
			]);
		}

		/**
		 * This method adds the modules to the context for latter use.
		 *
		 * @access public
		 * @param array $modules                                    the modules to be added
		 */
		public function addModules(array $modules) : void {
			$context = $this->current();
			$context['modules']->putEntries($modules);
		}

		/**
		 * This method returns the current context (which is the context at the top of the stack).
		 *
		 * @access protected
		 * @return array                                            the current context
		 */
		protected function current() : array {
			return $this->stack->peek();
		}

		/**
		 * This method returns the entity in the current context.
		 *
		 * @access public
		 * @return BT\Entity                                        the entity for the current
		 *                                                          context
		 */
		public function getEntity() : BT\Entity {
			$context = $this->current();
			return $context['entity'];
		}

		/**
		 * This method returns the module loading details for the specified key.
		 *
		 * @access public
		 * @param string $key                                       the name of the module to fetch
		 * @return array                                            the module's loading details
		 * @throws Throwable\KeyNotFound\Exception                  indicates that the key could not
		 *                                                          be found
		 */
		public function getModule(string $key) : array {
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

		/**
		 * This method returns the path to the current entity from the initial entity.
		 *
		 * @access public
		 * @return string                                           the path to the current entity
		 */
		public function getPath() : string {
			$context = $this->current();
			return $context['path'];
		}

		/**
		 * This method returns the term associated for the given key in the symbol table.
		 *
		 * @access public
		 * @param string $key                                       the key for which term should be
		 *                                                          returned
		 * @return mixed                                            the term for the given key
		 */
		public function getValue(string $key) {
			$stack = $this->stack->toList();
			for ($i = $stack->count() - 1; $i >= 0; $i--) {
				$context = $stack->getValue($i);
				$symbols = $context['symbols'];
				if ($symbols->hasKey($key)) {
					return $symbols->getValue($key);
				}
			}
			if (strlen($key) > 0) {
				switch ($key[0]) {
					case '@':
						return [];
					case '^':
						return [];
					case '?':
						return false;
					case '%':
						return [];
					case '*':
						return null;
					case '#':
						return 0;
				}
			}
			return Core\Data\Undefined::instance();
		}

		/**
		 * This method pops off the current context from the stack.
		 *
		 * @access public
		 */
		public function pop() : void {
			if ($this->stack->count() > 1) {
				$this->stack->pop();
			}
		}

		/**
		 * This method pushes onto the stack a new context with the entity for the given path.
		 *
		 * @access public
		 * @param string $path                                      the path to the sub-entity
		 */
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

		/**
		 * This method sets the value for the give key.
		 *
		 * @access public
		 * @param string $key                                       the key to be set
		 * @param mixed $value                                      the value to be set
		 */
		public function putEntry(string $key, $value) {
			$context = $this->current();
			$context['symbols']->putEntry($key, $value);
		}

	}

}