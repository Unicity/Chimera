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
	use \Unicity\OrderCalc;

	class CalculateFreightUsingKilograms extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting pounds to kilograms.
		 *
		 * @access public
		 * @const double
		 */
		const LBS_TO_KGS_CONVERSION_RATE = 2.2046;

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public function process(BT\Exchange $exchange) {
			$order = $exchange->getIn()->getBody()->Order;

			$weight = 0.0;

			foreach ($order->lines->items as $line) {
				$value = $line->item->weightEach->value;
				$unit = $line->item->weightEach->unit;
				if (preg_match('/^lb(s)?$/i', $unit)) {
					$value = $value / self::LBS_TO_KGS_CONVERSION_RATE;
				}
				else if (!preg_match('/^kg(s)?$/i', $unit)) {
					return BT\Status::ERROR;
				}
				$weight += $line->quantity * $value;
			}

			$breakpoint = Core\Convert::toDouble($this->policy->getValue('breakpoint'));
			$rate = Core\Convert::toDouble($this->policy->getValue('rate'));
			$surcharge = Trade\Money::make($this->policy->getValue('surcharge'), $order->currency);

			$weight = max(0.0, ceil($weight) - $breakpoint);

			$order->terms->freight->amount = Trade\Money::make($weight, $order->currency)
				->multiply($rate)
				->add($surcharge)
				->getConvertedAmount();

			return BT\Status::SUCCESS;
		}

	}

}