<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\VS;

	class StringTerm implements VS\Automaton\Term {

		protected $token;

		public function __construct(string $token) {
			$this->token = (strlen($token) > 2) ? substr($token, 1, -1) : '';
		}

		public function get0() {
			return $this->token;
		}

	}

}