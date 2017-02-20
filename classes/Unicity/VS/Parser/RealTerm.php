<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class RealTerm implements VS\Parser\Term {

		protected $token;

		public function __construct(string $token) {
			$this->token = doubleval($token);
		}

		public function get0() {
			return $this->token;
		}
	}

}