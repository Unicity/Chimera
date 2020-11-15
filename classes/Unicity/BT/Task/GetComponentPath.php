<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;

	class GetComponentPath extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$names = $this->policy->getValue('components');

			foreach ($names as $name) {
				$path = $engine->getEntity($entityId)->getComponentPath($name);
				if (is_string($path) && ($path != '')) {
					$blackboard = $engine->getBlackboard($this->policy->getValue('blackboard'));
					$key = $entityId . '.' . $name;
					$blackboard->putEntry($key, $path);
				}
			}

			return BT\Status::SUCCESS;
		}

	}

}