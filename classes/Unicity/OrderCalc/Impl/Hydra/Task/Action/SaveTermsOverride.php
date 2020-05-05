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

	class SaveTermsOverride extends BT\Task\Action {

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
				'Order.terms.pv',
				'Order.terms.subtotal',
				'Order.terms.taxableTotal',
				'Order.terms.timbre.amount',
				'Order.terms.total',
				'TermsOverride',
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

			$termsOverride = [];

			if ($this->policy->getValue('discount.amount') && $order->terms->discount->amount !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['discount.amount'] = $order->terms->discount->amount;
			}

			if ($this->policy->getValue('freight.amount') && $order->terms->freight->amount !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['freight.amount'] = $order->terms->freight->amount;
			}

			if ($this->policy->getValue('pv') && $order->terms->pv !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['pv'] = $order->terms->pv;
			}

			if ($this->policy->getValue('subtotal') && $order->terms->subtotal !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['subtotal'] = $order->terms->subtotal;
			}

			if ($this->policy->getValue('taxableTotal') && $order->terms->taxableTotal !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['taxableTotal'] = $order->terms->taxableTotal;
			}

			if ($this->policy->getValue('timbre.amount') && $order->terms->timbre->amount !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['timbre.amount'] = $order->terms->timbre->amount;
			}

			if ($this->policy->getValue('total') && $order->terms->total !== \Unicity\Core\Data\Undefined::instance()) {
				$termsOverride['total'] = $order->terms->total;
			}

			$entity->setComponent('TermsOverride', $termsOverride);

			return BT\Status::SUCCESS;
		}

	}

}
