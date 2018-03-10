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

namespace Unicity\Multithreading {

	use \Unicity\Core;

	class ThreadPool extends Core\AbstractObject {

		/**
		 * This variable stores the workers in the pool.
		 *
		 * @access protected
		 * @var array
		 */
		protected $workers;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->workers = array();
		}

		/**
		 * This method adds a worker to the pool.
		 *
		 * @access public
		 * @param \Thread $worker                                   the worker to be added
		 */
		public function add(\Thread $worker) {
			$this->workers[] = $worker;
		}

		/**
		 * This method cleans the pool of all workers.
		 *
		 * @access public
		 */
		public function clean() {
			foreach ($this->workers as $id => $worker) {
				if (!$worker->isRunning()) {
					unset($this->workers[$id]);
				}
			}
		}

		/**
		 * This method calls join on each worker in the pool.
		 *
		 * @access public
		 */
		public function join() {
			foreach ($this->workers as $id => $worker) {
				$this->workers[$id]->join();
			}
		}

		/**
		 * This method starts each worker in the pool.
		 *
		 * @access
		 */
		public function start() {
			foreach ($this->workers as $id => $worker) {
				$this->workers[$id]->start();
			}
		}

	}

}