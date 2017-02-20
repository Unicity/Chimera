<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class DefStatement implements VS\Parser\Statement {

		protected $args;

		public function __construct(array $args) {
			$this->args = $args;
		}

		public function accept0() : void {
			VS\Parser\SymbolTable::instance()->putEntry($this->args[0]->get0(), $this->args[1]->get0());
		}

	}

}