<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class EvalStatement implements VS\Parser\Statement {

		protected $args;

		public function __construct(array $args) {
			$this->args = $args;
		}

		public function accept0() : void {
			$class = $this->args[0]->get0();
			$object = new $class();
			call_user_func_array(
				[$object, 'test2'],
				[$this->args[1]->get0(), $this->args[2]->get0()]
			);
		}

	}

}