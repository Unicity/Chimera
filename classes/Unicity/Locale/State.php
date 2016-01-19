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
	use \Unicity\Locale;

	/**
	 * This class represents a state.
	 *
	 * @access public
	 * @class
	 * @package Locale
	 */
	class State extends Locale\Country {

		/**
		 * This variable stores any states that are cached.
		 *
		 * @access protected
		 * @var array
		 */
		protected static $states = array();

		/**
		 * This variable stores the state's data.
		 *
		 * @access protected
		 * @var array
		 */
		protected $state;

		/**
		 * This constructor initializes the class with the specified values.
		 *
		 * @access public
		 * @param string $state                                     the state to be encapsulated
		 * @param string $country                                   the country to be encapsulated
		 */
		public function __construct($state, $country = 'US') {
			$country = Core\Convert::toString($country);
			$info = Core\DataType::info($country);
			if (!array_key_exists($info->hash, static::$countries)) {
				static::$countries[$info->hash] = $this->getCountryData($country);
			}
			$this->country = static::$countries[$info->hash];
			$state = Core\Convert::toString($state);
			$info = Core\DataType::info($state);
			if (!array_key_exists($info->hash, static::$states)) {
				static::$states[$info->hash] = $this->getStateData($state, $this->country['CountryNumeric3']);
			}
			$this->state = static::$states[$info->hash];
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->state);
		}

		/**
		 * This method returns the state's code.
		 *
		 * @access public
		 * @return string                                           the state's code
		 */
		public function getStateCode() {
			return $this->state['StateCode'];
		}

		/**
		 * This method returns the translated value for the specified state.
		 *
		 * @access protected
		 * @param string $state                                     the state to be translated
		 * @param string $country                                   the country where the state is located
		 * @return mixed                                            the translated value for the specified
		 *                                                          state
		 */
		protected function getStateData($state, $country) {
			if ((is_integer($country) || (is_string($country) && ($country != ''))) && (is_integer($state) || (is_string($state)  && ($state != '')))) {
				$country = Core\Convert::toString($country);

				$state = Core\Convert::toString($state);
				$state = preg_replace('/\s+/', ' ', trim($state));

				$records = DB\SQL::select('locale')
					->from('States')
					->where('StateCode', '=', strtoupper($state))
					->where('CountryNumeric3', '=', $country)
					->limit(1)
					->query();

				if ($records->is_loaded()) {
					return $records->current();
				}

				$records = DB\SQL::select('locale')
					//->before(function(DB\Connection\Driver $driver) {
					//	$driver->get_resource()->createFunction('TRANSLITERATE', function($string) {
					//		return Common\StringRef::transliterate($string)->__toString();
					//	}, 1);
					//})
					->from('States')
					->where_block('(')
					->where(DB\SQL::expr('LOWER([StateName])'), '=', strtolower($state))
					//->where(DB\SQL::expr('LOWER(TRANSLITERATE([StateName]))'), '=', Common\StringRef::transliterate($state)->toLowerCase()->__toString(), 'OR')
					->where_block(')')
					->where('CountryNumeric3', '=', $country)
					->limit(1)
					->query();

				if ($records->is_loaded()) {
					return $records->current();
				}

				$records = DB\SQL::select('locale')
					->before(function(DB\Connection\Driver $driver) {
						$driver->get_resource()->createFunction('PREG_REPLACE', 'preg_replace', 3);
					})
					->from('States')
					->where(DB\SQL::expr("LOWER(PREG_REPLACE('/[^a-z]/i', '', [StateName]))"), '=', strtolower(preg_replace('/[^a-z]/i', '', $state)))
					->where('CountryNumeric3', '=', $country)
					->limit(1)
					->query();

				if ($records->is_loaded()) {
					return $records->current();
				}

				$records = DB\SQL::select('locale')
					//->before(function(DB\Connection\Driver $driver) {
					//	$driver->get_resource()->createFunction('TRANSLITERATE', function($string) {
					//		return Common\StringRef::transliterate($string)->__toString();
					//	}, 1);
					//})
					->from('States')
					->where_block('(')
					->where(DB\SQL::expr('LOWER([StateName])'), 'LIKE', '%' . strtolower($state) . '%')
					//->where(DB\SQL::expr('LOWER(TRANSLITERATE([StateName]))'), 'LIKE', '%' . Common\StringRef::transliterate($state)->toLowerCase() . '%', 'OR')
					->where_block(')')
					->where('CountryNumeric3', '=', $country)
					->limit(1)
					->query();

				if ($records->is_loaded()) {
					return $records->current();
				}
				/*
				$records = DB\SQL::select('locale')
					->before(function(DB\Connection\Driver $driver) {
						$driver->get_resource()->createFunction('SOUNDEX', 'soundex', 1);
					})
					->from('States')
					->where(DB\SQL::expr('SOUNDEX([StateName])'), '=', DB\SQL::expr("SOUNDEX('{$state}')"))
					->where('CountryNumeric3', '=', $country)
					->limit(1)
					->query();

				if ($records->is_loaded()) {
					return $records->current();
				}
				*/
			}
			return array(
				'StateID' => 0,
				'StateName' => null,
				'StateCode' => null,
				'CountryNumeric3' => null,
			);
		}

		/**
		 * This method returns the state's name.
		 *
		 * @access public
		 * @return string                                           the state's name
		 */
		public function getStateName() {
			return $this->state['StateName'];
		}

		/**
		 * This method returns the state's code.
		 *
		 * @access public
		 * @return string                                           the state's code
		 */
		public function __toString() {
			$state_code = $this->state['StateCode'];
			$state_code = ($state_code !== null) ? Core\Convert::toString($state_code) : '';
			return $state_code;
		}

	}

}