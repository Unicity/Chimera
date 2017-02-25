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

	class DoStatement extends VLD\Parser\Definition\Statement {

		protected $args;

		protected $block;

		public function __construct(VLD\Parser\Context $context, array $args, VLD\Parser\Definition\Block $block) {
			parent::__construct($context);
			$this->args = $args;
			$this->block = $block;
		}

		public function get() {
			$control = VLD\Parser\Definition\Control::getControl($this->args[0]->get());
			$paths = $this->args[1]->get();
			if (!is_array($paths)) {
				$paths = [$paths];
			}
			$policy = (isset($this->args[2])) ? $this->args[2]->get() : null;
			$statements = array();
			foreach ($paths as $path) {
				$statements[] = new VLD\Parser\Definition\SelectStatement($this->context, [new VLD\Parser\Definition\PathTerm($this->context, $path)], $this->block);
			}
			$object = new $control($this->context, $policy, $statements);
			return $object->get();
		}

	}

}