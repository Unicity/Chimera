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

	use \Unicity\IO;
	use \Unicity\VLD;

	class BlockVariable extends VLD\Parser\Definition\Block implements VLD\Parser\Definition\Variable {

		protected $token;

		public function __construct(VLD\Parser\Context $context, string $token) {
			parent::__construct($context);
			$this->token = $token;
		}

		public function get() {
			$value = $this->context->getValue($this->token);
			switch ($this->token[0]) {
				case '$':
					$parser = new VLD\Parser(new \Unicity\IO\FileReader(new IO\File($value)));
					return $parser->read($this->context);
				default:
					return $value;
			}
		}

	}

}