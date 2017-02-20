<?php

declare(strict_types = 1);

namespace Unicity\VS\Parser {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\VS;

	class Context extends Core\Object {

		protected $stack;

		public function __construct(BT\Entity $entity) {
			$this->stack = new Common\Mutable\Stack();
			$this->stack->push($entity);
		}

		public function current() : BT\Entity {
			return $this->stack->peek();
		}

		public function pop() : bool {
			if ($this->stack->count() > 1) {
				$this->stack->pop();
				return true;
			}
			return false;
		}

		public function push(string $path) {
			$this->stack->push($entity = new BT\Entity([
				'components' => $this->current()->getComponentAtPath($path),
				'entity_id' => $this->stack->count(),
			]));
		}

		public function root() : BT\Entity {
			return $this->stack->toList()->getValue(0);
		}

		protected static $singleton = null;

		public static function instance() : VS\Parser\Context {
			if (static::$singleton === null) {
				static::$singleton = new VS\Parser\Context();
			}
			return static::$singleton;
		}

	}

}