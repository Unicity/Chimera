<?php

declare(strict_types = 1);

namespace Unicity\EVT\Aggregator {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\EVT;

	abstract class AggregationStrategy extends Core\Object {

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
			if ($this->isSatisfied($this->list)) {
				$list = $this->list;
				$this->list = new Common\Mutable\ArrayList();
				$this->aggregate($list);
			}
		}

		abstract public function aggregate(Common\ArrayList $list) : void;

		public function isMatch($message, EVT\Context $context) : bool {
			return true;
		}

		abstract public function isSatisfied(Common\ArrayList $list) : bool;

	}

}