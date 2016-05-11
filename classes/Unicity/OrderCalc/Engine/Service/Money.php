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

namespace Unicity\OrderCalc\Engine\Service {

	use \Unicity\Core;
	use \Unicity\Trade;

	class Money {

		/**
		 * This method rounds the value to the nearest 5 cents' place.
		 *
		 * @access public
		 * @static
		 * @param double $value                                     the value to be rounded
		 * @param string $currency                                  the currency code
		 * @return double                                           the rounded value
		 *
		 * @see http://forums.devshed.com/php-development-5/round-nearest-5-cents-537959.html
		 */
		public static function roundToNearest5Cents($value, $currency) {
			$currency = new Trade\Currency($currency);

			return round(
				Core\Convert::toDouble($value) / 5,
				$currency->getDefaultFractionDigits(),
				PHP_ROUND_HALF_UP
			) * 5;
		}

		/**
		 * This method rounds the value to the nearest integer.
		 *
		 * @access public
		 * @static
		 * @param double $value                                     the value to be rounded
		 * @param string $currency                                  the currency code
		 * @return double                                           the rounded value
		 */
		public static function roundToNearestInteger($value, $currency) {
			return round(Core\Convert::toDouble($value));
		}

	}

}