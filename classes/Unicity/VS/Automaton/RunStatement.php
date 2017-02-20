<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\VS;

	class RunStatement implements VS\Automaton\Statement {

		protected $args;

		public function __construct(array $args) {
			$this->args = $args;
		}

		public function accept0() : void {
			call_user_func_array($this->args[0]->get0(), [$this->args[1]->get0(), $this->args[2]->get0()]);
		}

	}

}