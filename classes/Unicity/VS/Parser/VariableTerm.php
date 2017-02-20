<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class VariableTerm implements VS\Parser\Term {

		protected $token;

		public function __construct(string $token) {
			$this->token = $token;
		}

		public function get0() {
			return VS\Parser\SymbolTable::instance()->getValue($this->token);
		}

	}

}