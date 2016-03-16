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

	class CalculateFreightUsingPounds extends BT\Task\Action {

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
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$order = $entity->getBody()->Order;

			$freight = Trade\Money::make($order->terms->freight->amount, $order->currency);
			$weight = 0.0;

			foreach ($order->lines->items as $line) {
				$value = $line->item->weightEach->value;
				$unit = $line->item->weightEach->unit;
				if (!preg_match('/^kg(s)?$/i', $unit)) {
					$value = $value * self::KGS_TO_LBS_CONVERSION_RATE;
				}
				else if (preg_match('/^lb(s)?$/i', $unit)) {
					return BT\State\Error::with($entity);
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
				->add($freight)
				->getConvertedAmount();

			return BT\State\Success::with($entity);
		}

	}

}