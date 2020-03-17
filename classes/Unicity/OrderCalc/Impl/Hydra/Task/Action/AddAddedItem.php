<?php

/**
 * Copyright 2015-2016 Unicity International
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types = 1);

namespace Unicity\OrderCalc\Impl\Hydra\Task\Action {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\FP;
	use \Unicity\MappingService;
	use \Unicity\ORM;

	class AddAddedItem extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$order->added_lines->items->addValue(FP\IMap::fold($this->policy, function($carry, Common\Tuple $tuple) {
				ORM\Query::setValue($carry, $tuple->first(), $tuple->second());
				return $carry;
			}, new MappingService\Data\Model\JSON\HashMap('\\Unicity\\MappingService\\Impl\\Hydra\\API\\Master\\Model\\LineItem', true)));

			return BT\Status::SUCCESS;
		}

	}

}