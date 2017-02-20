<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\VS;

	class VariableTerm implements VS\Automaton\Term {

		protected $token;

		public function __construct(string $token) {
			$this->token = $token;
		}

		public function get0() {
			return VS\Automaton\SymbolTable::instance()->getValue($this->token);
		}

	}

}