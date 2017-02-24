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

namespace Unicity\VS\Parser\Definition {

	use \Unicity\VS;

	class EvalStatement extends VS\Parser\Definition\Statement {

		protected $args;

		public function __construct(VS\Parser\Context $context, array $args) {
			parent::__construct($context);
			$this->args = $args;
		}

		public function get() {
			$module = $this->context->getModule($this->args[0]->get());
			$policy = (isset($this->args[2])) ? $this->args[2]->get() : null;
			$object = new $module($policy);
			$entity = $this->context->getEntity();
			$root =$this->context->getPath();
			$paths = $this->args[1]->get();
			if (!is_array($paths)) {
				$paths = [$paths];
			}
			return call_user_func_array([$object, 'process'], [$entity, $root, $paths]);
		}

	}

}