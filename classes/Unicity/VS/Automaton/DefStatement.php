<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\Common;
	use \Unicity\VS;

	class DefStatement implements VS\Automaton\Statement {

		protected $args;

		public function __construct(array $args) {
			$this->args = $args;
		}

		public function accept0() : void {
			VS\Automaton\SymbolTable::instance()->putEntry($this->args[0]->get0(), $this->args[1]->get0());
		}

	}

}