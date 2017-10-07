<?php

declare(strict_types = 1);

namespace Unicity\EVT {

	use \Unicity\Core;
	use \Unicity\EVT;

	abstract class AggregationStrategy extends Core\Object {

		protected $oldExchange;

		public function __construct() {
			$this->oldExchange = new EVT\Exchange();
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->oldExchange);
		}

		public final function __invoke($message, EVT\Context $context) {
			$newExchange = new EVT\Exchange([
				'context' => $context,
				'message' => $message,
			]);

			if ($this->isMatch($newExchange)) {
				$this->oldExchange = $this->aggregate($this->oldExchange, $newExchange);
			}

			if ($this->isComplete($this->oldExchange)) {
				$this->onCompletion($this->oldExchange);
			}
		}

		abstract public function aggregate(EVT\Exchange $oldExchange, EVT\Exchange $newExchange) : EVT\Exchange;

		abstract public function isComplete(EVT\Exchange $exchange) : bool;

		public function isMatch(EVT\Exchange $exchange) : bool {
			return true;
		}

		abstract public function onCompletion(EVT\Exchange $exchange) : void;

	}

}