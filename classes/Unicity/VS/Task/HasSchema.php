<?php

declare(strict_types = 1);

namespace Unicity\VS\Task {

	use \Unicity\Common;

	class HasSchema implements Common\IBiPredicate {

		public function test2($t, $u) : bool {
			var_dump($t, $u);
			if ($t === $u) {
				return true;
			}
			return false;
		}

	}

}