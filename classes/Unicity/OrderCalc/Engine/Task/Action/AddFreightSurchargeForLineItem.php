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
	use \Unicity\Trade;

	class AddFreightSurchargeForLineItem extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param integer $entityId                                 the entity id being processed
		 * @param BT\Application $application                       the application running
		 * @return integer                                          the status
		 */
		public function process(int $entityId, BT\Application $application) {
			$order = $application->getEntity($entityId)->getComponent('Order');

			$freight = Trade\Money::make($order->terms->freight->amount, $order->currency);
			$items = $this->policy->getValue('items');
			$surcharge = $this->policy->getValue('surcharge');

			foreach ($order->lines->items as $line) {
				$item = trim(Core\Convert::toString($line->item->id->unicity));
				if ($items->hasValue($item)) {
					$freight = $freight->add(Trade\Money::make($surcharge * $line->quantity, $order->currency));
				}
			}

			$order->terms->freight->amount = $freight->getConvertedAmount();

			return BT\Status::SUCCESS;
		}

	}

}