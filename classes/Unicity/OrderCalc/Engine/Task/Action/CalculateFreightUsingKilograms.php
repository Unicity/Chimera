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

namespace Unicity\OrderCalc\Engine\Task\Action {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Trade;

	class CalculateFreightUsingKilograms extends BT\Task\Action {

		/**
		 * This constant represents the conversion rate for converting pounds to kilograms.
		 *
		 * @access public
		 * @const double
		 */
		const LBS_TO_KGS_CONVERSION_RATE = 2.2046;

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

			$freight = Trade\Money::make($order->terms->freight->amount, $order->currency);
			$breakpoint = Core\Convert::toDouble($this->policy->getValue('breakpoint'));
			$rate = Core\Convert::toDouble($this->policy->getValue('rate'));
			$surcharge = Trade\Money::make($this->policy->getValue('surcharge'), $order->currency);
			$round = Core\Convert::toBoolean($this->policy->getValue('round'));
			$weight = ($round) ? max(0.0, ceil($weight - $breakpoint)) : max(0.0, $weight - $breakpoint);

			$order->terms->freight->amount = Trade\Money::make($weight, $order->currency)
				->multiply($rate)
				->add($surcharge)
				->add($freight)
				->getConvertedAmount();

			return BT\Status::SUCCESS;
		}

	}

}