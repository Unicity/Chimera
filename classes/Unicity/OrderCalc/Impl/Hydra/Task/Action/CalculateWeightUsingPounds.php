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
	use \Unicity\Core;
	use \Unicity\ORM;

	class CalculateWeightUsingPounds extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting kilograms to pounds.
		 *
		 * @access protected
		 * @const double
		 */
		protected const KGS_TO_LBS_CONVERSION_RATE = 2.2046;

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [
				'Order.added_lines.items',
				'Order.lines.items',
			], $variants = [
				'Order.added_lines.aggregate.weight.unit',
				'Order.added_lines.aggregate.weight.value',
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
			try {
				$aggregate_weight_1 = $this->getAggregateWeight($order->lines->items);
				$order->lines->aggregate->weight->unit = 'lbs';
				$order->lines->aggregate->weight->value = $this->getRoundedWeight($aggregate_weight_1);

				$aggregate_weight_2 = $this->getAggregateWeight($order->added_lines->items);
				$order->added_lines->aggregate->weight->unit = 'lbs';
				$order->added_lines->aggregate->weight->value = $this->getRoundedWeight($aggregate_weight_2);

				$order->terms->weight = $this->getRoundedWeight($aggregate_weight_1 + $aggregate_weight_2);

				return BT\Status::SUCCESS;
			}
			catch (\Exception $ex) {
				return BT\Status::ERROR;
			}
		}

		private function getAggregateWeight($lines) : float {
			$aggregate_weight = 0.0;

			foreach ($lines as $line) {
				if (ORM\Query::hasPath($line, 'kitChildren.0.item.weightEach.unit')) {
					$aggregate_weight += $line->quantity * $this->getAggregateWeight($line->kitChildren);
				}
				else if (true
					&& !Core\Data\Toolkit::isUnset($line->item->weightEach)
					&& !Core\Data\Toolkit::isUnset($line->item->weightEach->value)
					&& !Core\Data\Toolkit::isUnset($line->item->weightEach->unit)
				) {
					$unit = $line->item->weightEach->unit;
					if (preg_match('/^kg(s)?$/i', $unit)) {
						$aggregate_weight += $line->quantity * ($line->item->weightEach->value * static::LBS_TO_KGS_CONVERSION_RATE);
					}
					else if (preg_match('/^lb(s)?$/i', $unit)) {
						$aggregate_weight += $line->quantity * $line->item->weightEach->value;
					}
				}
			}

			return $aggregate_weight;
		}

		private function getRoundedWeight($weight) : float {
			return round($weight, 6, PHP_ROUND_HALF_UP);
		}

	}

}
