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
	use \Unicity\Log;

	class ResetTotals extends BT\Task\Action {

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
			$this->aop['terms']['freight']['amount'] = $order->terms->freight->amount;
			$this->aop['terms']['tax']['amount'] = $order->terms->tax->amount;
			$this->aop['terms']['pretotal'] = $order->terms->pretotal;
			if ($order->terms->hasKey('timbre')) {
				$this->aop['terms']['timbre']['amount'] = $order->terms->timbre->amount;
			}
			$this->aop['terms']['total'] = $order->terms->total;
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

			$order->terms->discount->amount = 0.00;
			$order->terms->freight->amount = 0.00;
			$order->terms->tax->amount = 0.00;
			$order->terms->pretotal = 0.00;
			if ($order->terms->hasKey('timbre')) {
				$order->terms->timbre->amount = 0.00;
			}
			$order->terms->total = 0.00;

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
						'field' => 'terms.freight.amount',
						'from' => $this->aop['terms']['freight']['amount'],
						'to' => $order->terms->freight->amount,
					),
					array(
						'field' => 'terms.tax.amount',
						'from' => $this->aop['terms']['tax']['amount'],
						'to' => $order->terms->tax->amount,
					),
					array(
						'field' => 'terms.pretotal',
						'from' => $this->aop['terms']['pretotal'],
						'to' => $order->terms->pretotal,
					),
					array(
						'field' => 'terms.total',
						'from' => $this->aop['terms']['total'],
						'to' => $order->terms->total,
					),
				),
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'task' => 'action',
				'title' => $this->getTitle(),
			);

			if ($order->terms->hasKey('timbre')) {
				$message['changes'][] = array(
					'field' => 'terms.timbre.amount',
					'from' => $this->aop['terms']['timbre']['amount'],
					'to' => $order->terms->timbre->amount,
				);
			}

			Log\Logger::log(Log\Level::informational(), json_encode($message));
		}

	}

}