<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a task gambler.
	 *
	 * @access public
	 * @class
	 * @see http://php.net/manual/en/function.rand.php
	 * @see http://php.net/manual/en/function.mt-rand.php
	 */
	class Gambler extends BT\Task\Decorator {

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			parent::__construct($policy);
			if (!$this->policy->hasKey('callable')) {
				$this->policy->putEntry('callable', 'rand'); // ['rand', 'mt_rand']
			}
			if (!$this->policy->hasKey('odds')) {
				$this->policy->putEntry('odds', 0.01);
			}
			if (!$this->policy->hasKey('options')) {
				$this->policy->putEntry('options', 100);
			}
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$callable = explode(',', $this->policy->getValue('callable'));
			$options = Core\Convert::toInteger($this->policy->getValue('options'));
			$probability = Core\Convert::toDouble($this->policy->hasKey('odds')) * $options;
			if (call_user_func($callable, array(1, $options)) <= $probability) {
				return BT\Task\Handler::process($this->task, $engine, $entityId);
			}
			return BT\Status::ACTIVE;
		}

	}

}