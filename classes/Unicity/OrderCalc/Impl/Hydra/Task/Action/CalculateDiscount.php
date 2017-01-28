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
	use \Unicity\Core;
	use \Unicity\Log;
	use \Unicity\Trade;

	class CalculateDiscount extends BT\Task\Action {

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

			$this->aop['terms']['discount']['amount'] = $order->terms->discount->amount;
			$this->aop['terms']['discount']['percentage'] = $order->terms->discount->percentage;
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

			$discount_rate = Core\Convert::toDouble($this->policy->getValue('rate'));

			$order->terms->discount->amount = Trade\Money::make($order->terms->subtotal, $order->currency)
				->multiply($discount_rate)
				->getConvertedAmount();

			$order->terms->discount->percentage = $discount_rate * 100;

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
						'field' => 'terms.discount.amount',
						'from' => $this->aop['terms']['discount']['amount'],
						'to' => $order->terms->discount->amount,
					),
					array(
						'field' => 'terms.discount.percentage',
						'from' => $this->aop['terms']['discount']['percentage'],
						'to' => $order->terms->discount->percentage,
					),
				),
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy->toDictionary(),
				'status' => $joinPoint->getReturnedValue(),
				'task' => 'action',
				'title' => $this->getTitle(),
			);

			Log\Logger::log(Log\Level::informational(), json_encode($message));
		}

	}

}