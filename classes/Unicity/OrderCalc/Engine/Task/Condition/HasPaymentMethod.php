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

namespace Unicity\OrderCalc\Engine\Task\Condition {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\OrderCalc;

	class HasPaymentMethod extends BT\Task\Condition {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$order = $exchange->getIn()->getBody()->Order;

			$shippingMethods = $this->settings->getValue('methods');

			if (($order->transactions->items->count() > 0) && $shippingMethods->hasValue($order->transactions->items[0]->method)) {
				return BT\Task\Status::SUCCESS;
			}

			return BT\Task\Status::FAILED;
		}

	}

}
