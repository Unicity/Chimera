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

namespace Unicity\Locale {

	use \Leap\Core\DB;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents a currency.
	 *
	 * @access public
	 * @class
	 * @package Locale
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_4217
	 */
	class Currency extends Core\Object {

		/**
		 * This variable stores the currency's data.
		 *
		 * @access protected
		 * @var array
		 */
		protected $currency;

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param string $currency                                  the currency to be encapsulated
		 */
		public function __construct($currency) {
			$this->currency = $this->getCurrencyData($currency);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->currency);
		}

		/**
		 * This method returns ISO data for the specified currency.
		 *
		 * @access protected
		 * @param string $currency                                  the currency to be queried
		 * @return array                                            the data
		 */
		protected function getCurrencyData($currency) {
			if (is_string($currency)|| is_integer($currency)) {
				$currency = Core\Convert::toString($currency);
				$currency = preg_replace('/\s+/', ' ', trim($currency));
				$length = strlen($currency);

				if ($length == 3) {
					$records = DB\SQL::select('locale')
						->from('Currencies')
						->where('CurrencyNumeric3', '=', $currency)
						->where('CurrencyAlpha3', '=', strtoupper($currency), 'OR')
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}
				}
				else {
					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('TRANSLITERATE', function($string) {
								return Common\StringRef::transliterate($string)->__toString();
							}, 1);
						})
						->from('Currencies')
						->where(DB\SQL::expr('LOWER([CurrencyName])'), '=', strtolower($currency))
						->where(DB\SQL::expr("LOWER(TRANSLITERATE([CurrencyName]))"), '=', Common\StringRef::transliterate($currency)->toLowerCase()->__toString(), 'OR')
						->where(DB\SQL::expr('LOWER([CurrencySymbol])'), '=', strtolower($currency))
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}

					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('PREG_REPLACE', 'preg_replace', 3);
						})
						->from('Currencies')
						->where(DB\SQL::expr("LOWER(PREG_REPLACE('/[^a-z]/i', '', [CurrencyName]))"), '=', strtolower(preg_replace('/[^a-z]/i', '', $currency)))
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}

					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('TRANSLITERATE', function($string) {
								return Common\StringRef::transliterate($string)->__toString();
							}, 1);
						})
						->from('Currencies')
						->where(DB\SQL::expr('LOWER([CurrencyName])'), 'LIKE', '%' . strtolower($currency) . '%')
						->where(DB\SQL::expr("LOWER(TRANSLITERATE([CurrencyName]))"), 'LIKE', '%' . Common\StringRef::transliterate($currency)->toLowerCase() . '%', 'OR')
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}

					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('SOUNDEX', 'soundex', 1);
						})
						->from('Currencies')
						->where(DB\SQL::expr("SOUNDEX([CurrencyName])"), '=', DB\SQL::expr("SOUNDEX('{$currency}')"))
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}
				}
			}
			return array(
				'CurrencyID' => 0,
				'CurrencyName' => null,
				'CurrencyNumeric3' => null,
				'CurrencyAlpha3' => null,
				'CurrencySymbol' => null,
				'CurrencyDecimals' => -1,
			);
		}

		/**
		 * This method returns the maximum number of numbers after the decimal point.
		 *
		 * @access public
		 * @return integer                                           the maximum number of numbers
		 *                                                           after the decimal point
		 */
		public function getCurrencyDecimals() {
			return $this->currency['CurrencyDecimals']; // if "-1", then it is the same as a double in PHP
		}

		/**
		 * This method returns the 3-letter currency code as defined in ISO 4217.
		 *
		 * @access public
		 * @return string                                           the currency code
		 */
		public function getCurrencyISOAlpha3() {
			return $this->currency['CurrencyAlpha3'];
		}

		/**
		 * This method returns the 3-digit currency code as defined in ISO 4217.
		 *
		 * @access public
		 * @return string                                           the currency code
		 */
		public function getCurrencyISONumeric3() {
			return $this->currency['CurrencyNumeric3'];
		}

		/**
		 * This method returns the currency's name.
		 *
		 * @access public
		 * @return string                                           the currency's name
		 */
		public function getCurrencyName() {
			return $this->currency['CurrencyName'];
		}

		/**
		 * This method returns the currency's symbol.
		 *
		 * @access public
		 * @return string                                           the currency's symbol
		 */
		public function getCurrencySymbol() {
			return $this->currency['CurrencySymbol'];
		}

		/**
		 * This method returns the 2-letter currency code as defined in ISO 4217.
		 *
		 * @access public
		 * @return string                                           the currency code
		 */
		public function __toString() {
			$currency_alpha3 = $this->currency['CurrencyAlpha3'];
			$currency_alpha3 = ($currency_alpha3 !== null) ? $currency_alpha3 : '';
			return $currency_alpha3;
		}

		/**
		 * This method returns a number formatted as a currency string.
		 *
		 * @access public
		 * @static
		 * @param double $number                                    the number to be formatted
		 * @return string                                           the formatted number
		 *
		 * @see http://php.net/money_format
		 */
		public static function format($number) {
			$amount = Core\Convert::toDouble($number);
			if (function_exists('money_format')) {
				return money_format('%i', $amount);
			}
			return sprintf('%0.2f', $amount);
		}

	}

}