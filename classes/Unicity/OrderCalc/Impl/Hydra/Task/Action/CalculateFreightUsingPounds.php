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
	use \Unicity\Log;
	use \Unicity\Trade;

	class CalculateFreightUsingPounds extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting kilograms to pounds.
		 *
		 * @access public
		 * @const double
		 */
		const KGS_TO_LBS_CONVERSION_RATE = 2.2046;

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$order = $engine->getEntity($entityId)->getComponent('Order');

			$this->aop['terms']['freight']['amount'] = $order->terms->freight->amount;
		}

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

			$weight = 0.0;

			foreach ($order->lines->items as $line) {
				$value = $line->item->weightEach->value;
				$unit = $line->item->weightEach->unit;
				if (!preg_match('/^kg(s)?$/i', $unit)) {
					$value = $value * self::KGS_TO_LBS_CONVERSION_RATE;
				}
				else if (preg_match('/^lb(s)?$/i', $unit)) {
					return BT\Status::ERROR;
				}
				$weight += $line->quantity * $value;
			}

			$freight = Trade\Money::make($order->terms->freight->amount, $order->currency);
			$breakpoint = Core\Convert::toDouble($this->policy->getValue('breakpoint'));
			$rate = Core\Convert::toDouble($this->policy->getValue('rate'));
			$surcharge = Trade\Money::make($this->policy->getValue('surcharge'), $order->currency);
			$round = Core\Convert::toBoolean($this->policy->getValue('round'));
			$weight = ($round) ? max(0.0, ceil($weight - $breakpoint)) : max(0.0, $weight - $breakpoint);

			$order->terms->freight->amount = Trade\Money::make($weight, $order->currency)
				->multiply($rate)
				->add($surcharge)
				->add($freight)
				->getConvertedAmount();

			return BT\Status::SUCCESS;
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$order = $engine->getEntity($entityId)->getComponent('Order');

			$message = array(
				'changes' => array(
					array(
						'field' => 'Order.terms.freight.amount',
						'from' => $this->aop['terms']['freight']['amount'],
						'to' => $order->terms->freight->amount,
					),
				),
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'task' => 'action',
				'title' => $this->getTitle(),
			);

			Log\Logger::log(Log\Level::informational(), json_encode(Common\Collection::useArrays($message)));
		}

	}

}