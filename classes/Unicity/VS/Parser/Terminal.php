<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\Common;

	class Terminal implements Common\ISupplier {

		protected $token;

		public function __construct(string $token) {
			$this->token = $token;
		}

		public function get0() {
			return $this->token;
		}

	}

}