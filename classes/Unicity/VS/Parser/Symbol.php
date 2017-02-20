<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\Common;
	use \Unicity\VS;

	class Symbol implements Common\ISupplier {

		protected $token;

		public function __construct(string $token) {
			$this->token = false;
		}

		public function get0() {
			return $this->token;
		}

	}

}