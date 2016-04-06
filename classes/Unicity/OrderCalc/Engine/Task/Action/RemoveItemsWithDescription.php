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
	use \Unicity\Core;
	use \Unicity\FP;

	class RemoveItemsWithDescription extends BT\Task\Action {

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

			$descriptions = $this->policy->getValue('descriptions');

			$order->lines->items = FP\IList::filter($order->lines->items, function($line) use($descriptions) {
				$description = trim(Core\Convert::toString($line->catalogSlide->content->description));
				return $descriptions->hasValue($description);
			});

			return BT\Status::SUCCESS;
		}

	}

}