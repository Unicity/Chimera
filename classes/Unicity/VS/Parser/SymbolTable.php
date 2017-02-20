<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\Common;
	use \Unicity\Core;

	class SymbolTable extends Core\Object {

		protected static $table = null;

		public static function instance() : Common\Mutable\HashMap {
			if (static::$table === null) {
				static::$table = new Common\Mutable\HashMap();
			}
			return static::$table;
		}

	}

}