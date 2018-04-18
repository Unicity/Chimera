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

namespace Unicity\Locale {

	use \Leap\Core\DB;
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
			$info = Core\DataType::info([$state, $country]);
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

				$sql = DB\SQL::select('locale')
					->from('States')
					->where('CountryNumeric3', '=', $country)
					->limit(1);

				$code = preg_replace_callback('/./u', function (array $match) {
					$char = $match[0];
					if (ctype_punct($char)) {
						return '';
					}
					return $char;
				}, $state);

				if (preg_match('/^[A-Z]{3}$/i', $code)) {
					$sql = $sql->where('StateAlpha3', '=', strtoupper($code));
				}
				else if (preg_match('/^[A-Z]{2}$/i', $code)) {
					$sql = $sql->where('StateAlpha2', '=', strtoupper($code));
				}
				else {
					$sql = $sql->where('StateCode', '=', strtoupper($code));
				}

				$records = $sql->query();

				if ($records->is_loaded()) {
					return $records->current();
				}

				$state_1 = strtolower($state);
				$state_2 = strtr($state_1, array('saint' => 'st'));

				$records = DB\SQL::select('locale')
					//->before(function(DB\Connection\Driver $driver) {
					//	$driver->get_resource()->createFunction('TRANSLITERATE', function($string) {
					//		return Common\StringRef::transliterate($string)->__toString();
					//	}, 1);
					//})
					->from('States')
					->where_block('(')
					->where(DB\SQL::expr('LOWER([StateAlias])'), '=', $state_1)
					//->where(DB\SQL::expr('LOWER(TRANSLITERATE([StateAlias]))'), '=', Common\StringRef::transliterate($state)->toLowerCase()->__toString(), 'OR')
					->where(DB\SQL::expr('LOWER([StateAlias])'), '=', $state_2, 'OR')
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
					->where_block('(')
					->where(DB\SQL::expr("LOWER(PREG_REPLACE('/[^a-z]/i', '', [StateAlias]))"), '=', strtolower(preg_replace('/[^a-z]/i', '', $state_1)))
					->where(DB\SQL::expr("LOWER(PREG_REPLACE('/[^a-z]/i', '', [StateAlias]))"), '=', strtolower(preg_replace('/[^a-z]/i', '', $state_2)), 'OR')
					->where_block(')')
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
					->where(DB\SQL::expr('LOWER([StateAlias])'), 'LIKE', '%' . $state_1 . '%')
					->where(DB\SQL::expr('LOWER([StateAlias])'), 'LIKE', '%' . $state_2 . '%', 'OR')
					//->where(DB\SQL::expr('LOWER(TRANSLITERATE([StateAlias]))'), 'LIKE', '%' . Common\StringRef::transliterate($state)->toLowerCase() . '%', 'OR')
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
					->where(DB\SQL::expr('SOUNDEX([StateAlias])'), '=', DB\SQL::expr("SOUNDEX('{$state}')"))
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
				'StateAlpha2' => null,
				'StateAlpha3' => null,
				'StateName' => null,
				'StateCode' => null,
				'CountryNumeric3' => null,
			);
		}

		/**
		 * This method returns the 2-letter state code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the state's code
		 */
		public function getStateISOAlpha2() {
			return $this->state['StateAlpha2'];
		}

		/**
		 * This method returns the 3-letter state code as defined in ISO 3166.
		 *
		 * @access public
		 * @return string                                           the state's code
		 */
		public function getStateISOAlpha3() {
			return $this->state['StateAlpha3'];
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