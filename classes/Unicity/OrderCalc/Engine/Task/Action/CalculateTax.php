<?php

/**
 * Copyright 2015 Unicity International
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
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$order = $exchange->getIn()->getBody()->Order;

			$tax_amount = Trade\Money::make($order->terms->subtotal, $order->currency)
				->add(Trade\Money::make($order->terms->freight->amount, $order->currency));

			$tax_rate = Core\Convert::toDouble($this->policy->getValue('rate'));

			$tax_amount = $tax_amount->multiply($tax_rate);

			$order->terms->tax->amount = $tax_amount->getConvertedAmount();

			return BT\Task\Status::SUCCESS;
		}

	}

}