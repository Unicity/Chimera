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

namespace Unicity\OrderCalc\Engine\Task\Action {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\FP;

	class AddWeightToLineItem extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) {
			$order = $engine->getEntity($entityId)->getComponent('Order');

			$items = $this->policy->getValue('items');
			$items = FP\IList::foldLeft($items, function(Common\Mutable\HashMap $c, string $x) {
				$v = array_map('trim', explode(',', $x));
				$c->putEntry($v[0], floatval($v[1]));
				return $c;
			}, new Common\Mutable\HashMap());
			$unit = $this->policy->getValue('unit'); // 'kg' or 'lbs'

			foreach ($order->lines->items as $line) {
				$item = trim(Core\Convert::toString($line->item->id->unicity));
				if ($items->hasKey($item)) {
					$line->item->weightEach->value = $items->getValue($item);
					$line->item->weightEach->unit = $unit;
				}
			}

			return BT\Status::SUCCESS;
		}

	}

}