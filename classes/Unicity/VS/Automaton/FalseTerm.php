<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\VS;

	class FalseTerm implements VS\Automaton\Term {

		protected $token;

		public function __construct() {
			$this->token = false;
		}

		public function get0() {
			return $this->token;
		}

	}

}