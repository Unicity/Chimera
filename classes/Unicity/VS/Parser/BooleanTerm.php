<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use Unicity\Core;
	use \Unicity\VS;

	class BooleanTerm implements VS\Parser\Term {

		protected $token;

		public function __construct(string $token) {
			$this->token = ($token === 'true');
		}

		public function get0() {
			return $this->token;
		}

	}

}