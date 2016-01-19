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

	class CalculateWeightUsingPounds extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting kilograms to pounds.
		 *
		 * @access public
		 * @const double
		 */
		const KGS_TO_LBS_CONVERSION_RATE = 2.2046;

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
				$uom = $line->item->weightEach->unit;
				if (!preg_match('/^kg(s)?$/i', $uom)) {
					$value = $value * self::KGS_TO_LBS_CONVERSION_RATE;
				}
				else if (preg_match('/^lb(s)?$/i', $uom)) {
					return BT\Task\Status::ERROR;
				}
				$weight += $line->quantity * $value;
			}

			$order->lines->aggregate->weight->unit = 'lbs';
			$order->lines->aggregate->weight->value = $weight;

			return BT\Task\Status::SUCCESS;
		}

	}

}