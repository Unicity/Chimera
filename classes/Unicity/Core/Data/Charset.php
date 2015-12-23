<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\Core\Data {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;
	use \Unicity\Throwable;

	/**
	 * This class handles encoding between character sets.
	 *
	 * @access public
	 * @class
	 * @package Core
	 *
	 * @see https://www.gnu.org/software/libiconv/
	 * @see https://gist.github.com/hakre/4188459
	 * @see http://php.net/manual/en/mbstring.supported-encodings.php
	 * @see http://intranet/index.php/Code_Page
	 * @see http://svn.python.org/projects/python/tags/r32rc1/Lib/encodings/aliases.py
	 */
	class Charset extends Core\Object {

		/**
		 * This constant defines the encoding for BIG5.
		 *
		 * @access public
		 * @const string
		 */
		const BIG5_ENCODING = 'BIG5';

		/**
		 * This constant defines the encoding for CP437.
		 *
		 * @access public
		 * @const string
		 */
		const CP437_ENCODING = 'CP437';

		/**
		 * This constant defines the encoding for EUC-KR.
		 *
		 * @access public
		 * @const string
		 */
		const EUC_KR_ENCODING = 'EUC-KR';

		/**
		 * This constant defines the encoding for ISO-8859-1.
		 *
		 * @access public
		 * @const string
		 */
		const ISO_8859_1_ENCODING = 'ISO-8859-1';

		/**
		 * This constant defines the encoding for SHIFT_JIS.
		 *
		 * @access public
		 * @const string
		 */
		const SHIFT_JIS_ENCODING = 'SHIFT_JIS';

		/**
		 * This constant defines the encoding for UCS-4BE.
		 *
		 * @access public
		 * @const string
		 */
		const UCS_4BE_ENCODING = 'UCS-4BE';

		/**
		 * This constant defines the encoding for UTF-8.
		 *
		 * @access public
		 * @const string
		 */
		const UTF_8_ENCODING = 'UTF-8';

		/**
		 * This constant defines the encoding for Windows-874.
		 *
		 * @access public
		 * @const string
		 */
		const WINDOWS_874_ENCODING = 'Windows-874';

		/**
		 * This constant defines the encoding for Windows-1252.
		 *
		 * @access public
		 * @const string
		 */
		const WINDOWS_1252_ENCODING = 'Windows-1252';

		/**
		 * This constant defines the encoding for Windows-1254.
		 *
		 * @access public
		 * @const string
		 */
		const WINDOWS_1254_ENCODING = 'Windows-1254';

		/**
		 * This constant defines the encoding for Windows-1252.
		 *
		 * @access public
		 * @const string
		 */
		const WINDOWS_1258_ENCODING = 'Windows-1258';

		/**
		 * This method encodes a string into the specified encoding from another encoding system.
		 *
		 * @access public
		 * @param string $string                                    the string to be encoded
		 * @param string $source_encoding                           the source encoding
		 * @param string $target_encoding                           the target encoding
		 * @return string                                           the encoded string
		 */
		public static function encode($string, $source_encoding, $target_encoding) {
			if (is_string($string) && ($string != '')) {
				if (strcasecmp($source_encoding, $target_encoding) != 0) {
					if (function_exists('iconv')) {
						$string = @iconv($source_encoding, $target_encoding . '//IGNORE//TRANSLIT', $string);
					}
					else if (function_exists('mb_convert_encoding')) {
						$string = mb_convert_encoding($string, $target_encoding, $source_encoding);
					}
				}
				if (strcasecmp($target_encoding, static::UTF_8_ENCODING) == 0) { // http://stackoverflow.com/questions/1523460/ensuring-valid-utf-8-in-php
					/*
						[\x09\x0A\x0D\x20-\x7E]            # ASCII
						[\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
						\xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
						[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
						\xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
						\xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
						[\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
						\xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
					*/
					$blacklist = array('%E0%B8%BA', '%E2%80%8B'); // known non-printable/hidden characters

					$string = preg_replace_callback('/./u', function (array $match) use ($blacklist) {
						$char = $match[0];
						if (in_array(urlencode($char), $blacklist)) {
							return '';
						}
						return $char;
					}, $string);

					if ($string === null) {
						$string = '';
					}
				}
			}
			return $string;
		}

	}

}
