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

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\FP;
	use \Unicity\IO;

	class NormalizeTaxValues extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [], $variants = [
				'Order.lines.items',
				'Order.added_lines.items',
				'Order.terms.freight.tax',
				'Order.terms.tax.amount',
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

			$lines = $order->lines->items;
			if (!Core\Data\ToolKit::isEmpty($order->added_lines->items)) {
				$lines = FP\IList::appendAll($order->lines->items, $order->added_lines->items);
			}

			foreach ($lines as $line) {
				if (Core\Data\ToolKit::isEmpty($line->terms->tax->aggregate->items)) {
					$line->terms->tax->aggregate->items = new Common\Mutable\ArrayList();
				}
				if (Core\Data\ToolKit::isEmpty($line->terms->tax->aggregate->amount)) {
					$line->terms->tax->aggregate->amount = 0.0;
				}

				if (!Core\Data\ToolKit::isEmpty($line->kitChildren)) {
					foreach ($line->kitChildren as $childLine) {
						if (Core\Data\ToolKit::isEmpty($childLine->terms->tax->aggregate->items)) {
							$childLine->terms->tax->aggregate->items = new Common\Mutable\ArrayList();
						}
						if (Core\Data\ToolKit::isEmpty($childLine->terms->tax->aggregate->amount)) {
							$childLine->terms->tax->aggregate->amount = 0.0;
						}
					}
				}
			}

			if (Core\Data\ToolKit::isEmpty($order->terms->freight->terms->tax->aggregate->items)) {
				$order->terms->freight->terms->tax->aggregate->items = new Common\Mutable\ArrayList();
			}
			if (Core\Data\ToolKit::isEmpty($order->terms->freight->terms->tax->aggregate->amount)) {
				$order->terms->freight->terms->tax->aggregate->amount = 0.0;
			}

			if (Core\Data\ToolKit::isEmpty($order->terms->tax->amount)) {
				$order->terms->tax->amount = 0.0;
			}

			return BT\Status::SUCCESS;
		}

	}

}
