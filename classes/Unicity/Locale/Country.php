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
	 * This class represents a country.
	 *
	 * @access public
	 * @class
	 * @package Locale
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1_numeric
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3
	 */
	class Country extends Core\Object {

		/**
		 * This variable stores any countries that are cached.
		 *
		 * @access protected
		 * @var array
		 */
		protected static $countries = array();

		protected static $legacy = array(
			'DR'   => 'DO',
			'CHEP' => 'CH',
			'GER'  => 'DE',
			'PHI'  => 'PH',
		);

		/**
		 * This variable stores the country's data.
		 *
		 * @access protected
		 * @var array
		 */
		protected $country;

		/**
		 * This constructor initializes the class with the specified value.
		 *
		 * @access public
		 * @param string $country                                   the country to be encapsulated
		 */
		public function __construct($country) {
			$country = Core\Convert::toString($country);
			$info = Core\DataType::info($country);
			$hash = $info->hash;
			if (!isset(static::$countries[$hash])) {
				static::$countries[$hash] = $this->getCountryData($country);
			}
			$this->country = static::$countries[$hash];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->country);
		}

		/**
		 * This method returns ISO data for the specified country.
		 *
		 * @access protected
		 * @param string $country                                   the country to be queried
		 * @return array                                            the data
		 */
		protected function getCountryData($country) {
			if (is_integer($country) || (is_string($country) && ($country != ''))) {
				$country = Core\Convert::toString($country);
				$country = preg_replace('/\s+/', ' ', trim($country));
				$key = strtoupper($country);
				if (isset(static::$legacy[$key])) {
					$country = static::$legacy[$key];
				}
				$length = strlen($country);

				if ($length == 2) {
					$records = DB\SQL::select('locale')
						->from('Countries')
						->where('CountryAlpha2', '=', strtoupper($country))
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}
				}
				else if ($length == 3) {
					$records = DB\SQL::select('locale')
						->from('Countries')
						->where('CountryNumeric3', '=', $country)
						->where('CountryAlpha3', '=', strtoupper($country), 'OR')
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
						->from('Countries')
						->where(DB\SQL::expr('LOWER([CountryName])'), '=', strtolower($country))
						->where(DB\SQL::expr("LOWER(TRANSLITERATE([CountryName]))"), '=', Common\StringRef::transliterate($country)->toLowerCase()->__toString(), 'OR')
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}

					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('PREG_REPLACE', 'preg_replace', 3);
						})
						->from('Countries')
						->where(DB\SQL::expr("LOWER(PREG_REPLACE('/[^a-z]/i', '', [CountryName]))"), '=', strtolower(preg_replace('/[^a-z]/i', '', $country)))
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
						->from('Countries')
						->where(DB\SQL::expr('LOWER([CountryName])'), 'LIKE', '%' . strtolower($country) . '%')
						->where(DB\SQL::expr("LOWER(TRANSLITERATE([CountryName]))"), 'LIKE', '%' . Common\StringRef::transliterate($country)->toLowerCase() . '%', 'OR')
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}

					$records = DB\SQL::select('locale')
						->before(function(DB\Connection\Driver $driver) {
							$driver->get_resource()->createFunction('SOUNDEX', 'soundex', 1);
						})
						->from('Countries')
						->where(DB\SQL::expr("SOUNDEX([CountryName])"), '=', DB\SQL::expr("SOUNDEX('{$country}')"))
						->limit(1)
						->query();

					if ($records->is_loaded()) {
						return $records->current();
					}
				}
			}
			return array(
				'CountryID' => 0,
				'CountryName' => null,
				'CountryNumeric3' => null,
				'CountryAlpha2' => null,
				'CountryAlpha3' => null,
			);
		}

		/**
		 * This method returns the 2-letter country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function getCountryISOAlpha2() {
			return $this->country['CountryAlpha2'];
		}

		/**
		 * This method returns the 3-letter country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function getCountryISOAlpha3() {
			return $this->country['CountryAlpha3'];
		}

		/**
		 * This method returns the 3-digit country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function getCountryISONumeric3() {
			return $this->country['CountryNumeric3'];
		}

		/**
		 * This method returns the country's name.
		 *
		 * @access public
		 * @return string                                           the country's name
		 */
		public function getCountryName() {
			return $this->country['CountryName'];
		}

		/**
		 * This method returns the 2-letter country code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the country code
		 */
		public function __toString() {
			$country_alpha2 = $this->country['CountryAlpha2'];
			$country_alpha2 = ($country_alpha2 !== null) ? $country_alpha2 : '';
			return $country_alpha2;
		}

	}

}