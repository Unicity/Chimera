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

	class CalculateTaxablePriceEach extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [], $variants = [
				'Order.lines.items',
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

			foreach ($order->lines->items as $line) {
				// using count here because empty doesn't work because of the type of $order
				if (count($line->kitChildren ?? []) !== 0) {
					$taxablePriceEach = Trade\Money::make(0, $order->currency);

					foreach ($line->kitChildren as $childLine) {
						$taxablePriceEach = $taxablePriceEach->add(Trade\Money::make($childLine->terms->taxablePriceEach * $childLine->quantity, $order->currency));
					}

					$line->terms->taxablePriceEach = $taxablePriceEach->getConvertedAmount();
				}
			}

			return BT\Status::SUCCESS;
		}

	}

}