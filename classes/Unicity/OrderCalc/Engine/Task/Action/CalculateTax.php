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

namespace Unicity\OrderCalc\Engine\Task\Action {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Trade;

	class CalculateTax extends BT\Task\Action {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$order = $entity->getBody()->Order;

			$tax_rate = Core\Convert::toDouble($this->policy->getValue('rate'));

			$order->terms->tax->amount = Trade\Money::make($order->terms->subtotal, $order->currency)
				->add(Trade\Money::make($order->terms->freight->amount, $order->currency))
				->multiply($tax_rate)
				->getConvertedAmount();

			$order->terms->tax->percentage = $tax_rate * 100;

			return BT\State\Success::with($entity);
		}

	}

}