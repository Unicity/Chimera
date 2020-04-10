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

	class ResetTerms extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [], $variants = [
				'Order.terms.discount.amount',
				'Order.terms.freight.amount',
				'Order.terms.pretotal',
				'Order.terms.pv',
				'Order.terms.subtotal',
				'Order.terms.tax.amount',
				'Order.terms.taxableTotal',
				'Order.terms.timbre.amount',
				'Order.terms.total',
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
			$order = $engine->getEntity($entityId)->getComponent('Order');

			if ($this->policy->getValue('discount.amount')) {
				$order->terms->discount->amount = 0.00;
			}

			if ($this->policy->getValue('freight.amount')) {
				$order->terms->freight->amount = 0.00;
			}

			if ($this->policy->getValue('pretotal')) {
				$order->terms->pretotal = 0.00;
			}

			if ($this->policy->getValue('pv')) {
				$order->terms->pv = 0.00;
			}

			if ($this->policy->getValue('subtotal')) {
				$order->terms->subtotal = 0.00;
			}

			if ($this->policy->getValue('tax.amount')) {
				$order->terms->tax->amount = 0.00;
			}

			if ($this->policy->getValue('taxableTotal')) {
				$order->terms->taxableTotal = 0.00;
			}

			if ($this->policy->getValue('timbre.amount')) {
				$order->terms->timbre->amount = 0.00;
			}

			if ($this->policy->getValue('total')) {
				$order->terms->total = 0.00;
			}

			return BT\Status::SUCCESS;
		}

	}

}