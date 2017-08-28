<?php

declare(strict_types = 1);

namespace Unicity\EVT\Aggregator {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\EVT;

	abstract class ListAggregationStrategy extends Core\Object {

		protected $list;

		public function __construct() {
			$this->list = new Common\Mutable\ArrayList();
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->list);
		}

		public final function __invoke($message, EVT\Context $context) {
			if ($this->isMatch($message, $context)) {
				$this->list->addValue($message);
			}
			if ($this->isComplete($this->list)) {
				$list = $this->list;
				$this->list = new Common\Mutable\ArrayList();
				$this->aggregate($list);
			}
		}

		abstract public function aggregate(Common\ArrayList $list) : void;

		abstract public function isComplete(Common\ArrayList $list) : bool;

		public function isMatch($message, EVT\Context $context) : bool {
			return true;
		}

	}

}