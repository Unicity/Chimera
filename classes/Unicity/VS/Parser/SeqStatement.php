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

	use \Unicity\VS;

	class SeqStatement implements VS\Parser\Statement {

		protected $args;

		public function __construct(array $args) {
			$this->args = $args;
		}

		public function get0() {
			$task = $this->args[0]->get0();
			$policy = (isset($this->args[2])) ? $this->args[2]->get0() : null;
			$context = VS\Parser\Context::instance();
			$output = $context->results();
			$entity = $context->current();
			$other = $this->args[1]->get0();

			$object = new $task($policy, $output);
			return call_user_func_array([$object, 'process'], [$entity, $other]);
		}

	}

}