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

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Trade;

	class DiscountItemPartialAmount extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [
				'Order.currency',
				'Order.lines.items',
			], $variants = [
				'Order.terms.discount.amount',
			]);
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
			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$item = $this->policy->getValue('item');
			$amount = $this->policy->getValue('amount');

			$quantity = 0;

			foreach ($order->lines->items as $index => $line) {
				if (($item == $line->item->id->unicity) && ($line->quantity > 0)) {
					$quantity += $line->quantity;
				}
			}

			$order->terms->discount->amount = Trade\Money::make($order->terms->discount->amount, $order->currency)
				->add(Trade\Money::make($quantity * $amount, $order->currency))
				->getConvertedAmount();

			return BT\Status::SUCCESS;
		}

	}

}
