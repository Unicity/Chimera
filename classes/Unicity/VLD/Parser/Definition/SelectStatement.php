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

namespace Unicity\VLD\Parser\Definition {

	use \Unicity\VLD;

	class SelectStatement extends VLD\Parser\Definition\Statement {

		protected $args;

		protected $statements;

		public function __construct(VLD\Parser\Context $context, array $args, array $statements) {
			parent::__construct($context);
			$this->args = $args;
			$this->statements = $statements;
		}

		public function get() {
			$path = (isset($this->args[0])) ? $this->args[0]->get() : null;
			$this->context->push($path);

			$object = new VLD\Parser\Definition\SeqControl($this->context, null, $this->statements);
			$feedback = $object->get();

			$this->context->pop();

			return $feedback;
		}

	}

}