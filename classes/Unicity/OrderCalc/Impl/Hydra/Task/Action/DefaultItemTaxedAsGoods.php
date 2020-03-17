<?php

/**
 * Copyright 2015-2020 Unicity International
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

	class DefaultItemTaxedAsGoods extends BT\Task\Action {

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

			foreach ($order->lines->items as $line) {
				if (!$line->item->hasKey('taxedAs') || !in_array($line->item->taxedAs, ['service', 'goods'])) {
					$line->item->taxedAs = 'goods';
				}
			}

			foreach ($order->added_lines->items as $line) {
				if (!$line->item->hasKey('taxedAs') || !in_array($line->item->taxedAs, ['service', 'goods'])) {
					$line->item->taxedAs = 'goods';
				}
			}

			return BT\Status::SUCCESS;
		}

	}

}