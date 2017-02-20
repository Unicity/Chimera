<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

	use \Unicity\Common;
	use \Unicity\VS;

	class MapTerm implements VS\Automaton\Term {

		protected $entries;

		public function __construct(array $entries) {
			$this->entries = $entries;
		}

		public function get0() {
			$map = array();
			foreach ($this->entries as $entry) {
				$map[$entry->first()->get0()] = $entry->second()->get0();
			}
			return $map;
		}

	}

}