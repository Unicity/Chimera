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

	class CalculateWeightUsingKilograms extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting pounds to kilograms.
		 *
		 * @access protected
		 * @const double
		 */
		protected const LBS_TO_KGS_CONVERSION_RATE = 2.2046;

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [
				'Order.lines.items',
			], $variants = [
				'Order.lines.aggregate.weight.unit',
				'Order.lines.aggregate.weight.value',
				'Order.terms.weight',
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

			$weight = 0.0;

			foreach ($order->lines->items as $line) {
				$value = $line->item->weightEach->value;
				$unit = $line->item->weightEach->unit;
				if (preg_match('/^lb(s)?$/i', $unit)) {
					$value = $value / self::LBS_TO_KGS_CONVERSION_RATE;
				}
				else if (!preg_match('/^kg(s)?$/i', $unit)) {
					return BT\Status::ERROR;
				}
				$weight += $line->quantity * $value;
			}

			$weight = round($weight, 6, PHP_ROUND_HALF_UP);

			$order->lines->aggregate->weight->unit = 'kg';
			$order->lines->aggregate->weight->value = $weight;
			$order->terms->weight = $weight;

			return BT\Status::SUCCESS;
		}

	}

}
