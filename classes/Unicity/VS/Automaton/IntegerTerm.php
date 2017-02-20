<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\VS;

	class IntegerTerm extends VS\Automaton\RealTerm {

		public function __construct(string $token) {
			$this->token = intval($token);
		}

		public function get0() {
			return $this->token;
		}

	}

}