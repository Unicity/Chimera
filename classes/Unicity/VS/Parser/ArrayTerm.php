<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\VS;

	class ArrayTerm implements VS\Parser\Term {

		protected $terms;

		public function __construct(array $terms) {
			$this->terms = $terms;
		}

		public function get0() {
			return array_map(function(VS\Parser\Term $term) {
				return $term->get0();
			}, $this->terms);
		}

	}

}