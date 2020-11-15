<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;
	use \Unicity\Config;
	use \Unicity\Core;

	class Marshaller extends BT\Task\Responder {

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

			$components = $engine->getEntity($entityId)->getComponents();
			$policy = Core\Convert::toDictionary($this->policy);

			$writer = new Config\JSON\Writer($components);
			$writer->config($policy);
			$writer->export($response);

			return BT\Status::QUIT;
		}

	}

}
