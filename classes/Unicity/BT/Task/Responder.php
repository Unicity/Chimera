<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Core;

	/**
	 * This class represents a task responder.
	 *
	 * @access public
	 * @class
	 */
	class Responder extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$response = $engine->getResponse();

			$response->setStatus(Core\Convert::toInteger($this->policy->getValue('status')));
			$response->setBody($this->policy->getValue('body'));

			return BT\Status::QUIT;
		}

	}

}