<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class IntegerTerm extends VS\Parser\RealTerm {

		public function __construct(string $token) {
			$this->token = intval($token);
		}

		public function get0() {
			return $this->token;
		}

	}

}