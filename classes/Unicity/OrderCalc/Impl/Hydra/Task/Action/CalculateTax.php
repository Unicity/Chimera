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

	class CalculateTax extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$this->aop['terms']['tax']['amount'] = $order->terms->tax->amount;
			$this->aop['terms']['tax']['percentage'] = $order->terms->tax->percentage;
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
			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$tax_rate = Core\Convert::toDouble($this->policy->getValue('rate'));

			$order->terms->tax->amount = Trade\Money::make($order->terms->subtotal, $order->currency)
				->subtract(Trade\Money::make($order->terms->discount->amount, $order->currency))
				->add(Trade\Money::make($order->terms->freight->amount, $order->currency))
				->multiply($tax_rate)
				->getConvertedAmount();

			$order->terms->tax->percentage = $tax_rate * 100;

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

			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$message = array(
				'changes' => array(
					array(
						'field' => 'Order.terms.tax.amount',
						'from' => $this->aop['terms']['tax']['amount'],
						'to' => $order->terms->tax->amount,
					),
					array(
						'field' => 'Order.terms.tax.percentage',
						'from' => $this->aop['terms']['tax']['percentage'],
						'to' => $order->terms->tax->percentage,
					),
				),
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'tags' => array(),
				'title' => $this->getTitle(),
			);

			$blackboard = $engine->getBlackboard('global');
			if ($blackboard->hasKey('tags')) {
				$tags = $blackboard->getValue('tags');
				foreach ($tags as $path) {
					$message['tags'][] = array(
						'name' => $path,
						'value' => $entity->getComponentAtPath($path),
					);
				}
			}

			Log\Logger::log(Log\Level::informational(), json_encode(Common\Collection::useArrays($message)));
		}

	}

}