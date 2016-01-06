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

	use \Unicity\Locale;

	/**
	 * This class represents a language.
	 *
	 * @access public
	 * @class
	 * @package Locale
	 *
	 * @see http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
	 */
	class Language extends Locale\Country {

		/**
		 * This variable stores the language.
		 *
		 * @access protected
		 * @var array
		 */
		protected $language;

		/**
		 * This constructor initializes the class with the specified values.
		 *
		 * @access public
		 * @param string $language                                  the language to be encapsulated
		 * @param string $country                                   the country to be encapsulated
		 */
		public function __construct($language, $country = null) {
			parent::__construct($country);
			$this->language = $language; // TODO add logic to lookup language codes
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->language);
		}

		/**
		 * This method returns the 2-letter language code as defined in ISO 639-1.
		 *
		 * @access public
		 * @return string                                           the language code
		 *
		 * @see http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
		 */
		public function getLanguageISOAlpha2() {
			return $this->language['LanguageAlpha2'];
		}

		/**
		 * This method returns the 3-letter language code as defined in ISO 639-2/3.
		 *
		 * @access public
		 * @return string                                           the language code
		 *
		 * @see http://en.wikipedia.org/wiki/List_of_ISO_639-2_codes
		 */
		public function getLanguageISOAlpha3() {
			return $this->language['LanguageAlpha3'];
		}

		/**
		 * This method returns the language's name.
		 *
		 * @access public
		 * @return string                                           the language's name
		 */
		public function getLanguageName() {
			return $this->language['LanguageName'];
		}

		/**
		 * This method returns the 2-letter language code as defined in ISO 639-1, plus the 2-letter
		 * country code as defined in ISO 3166 (if available).
		 *
		 * @access public
		 * @return string                                           the language code
		 */
		public function __toString() {
			$language_alpha2 = $this->language['LanguageAlpha2'];
			if ($language_alpha2 !== null) {
				$country_alpha2 = $this->country['CountryAlpha2'];
				return ($country_alpha2 !== null)
					? $language_alpha2 . '-' . $country_alpha2
					: $language_alpha2;
			}
			return '';
		}

	}

}