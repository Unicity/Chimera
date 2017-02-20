<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class NullTerm implements VS\Parser\Term {

		protected $token;

		public function __construct() {
			$this->token = null;
		}

		public function get0() {
			return $this->token;
		}

	}

}