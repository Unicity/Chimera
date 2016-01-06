<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2012 Spadefoot Team
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

namespace Unicity\Barcode\Code39 {

	use \Unicity\Barcode;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to create a Code 39 barcode.
	 *
	 * @access public
	 * @class
	 * @package Barcode
	 */
	class Creator extends Barcode\Creator {

		/**
		 * This variable stores the properties associated with the barcode.
		 *
		 * @access protected
		 * @var array
		 */
		protected $properties;

		/**
		 * Initializes this barcode creator.
		 *
		 * @access public
		 * @param string $data                                      the data string to be encoded
		 * @param array $properties                                 the properties used when rendering
		 *                                                          the barcode
		 * @throws Throwable\InvalidArgument\Exception              indicates that the data could not
		 *                                                          be encoded
		 */
		public function __construct($data, array $properties = array()) {
			$data = Core\Convert::toString($data);
			if (!preg_match('/^[- *$%.\/+a-z0-9]+$/i', $data)) {
				throw new Throwable\InvalidArgument\Exception('Invalid character in data string. Expected an data in the Code 39 subset, but got ":data" value.', array(':data' => $data));
			}
			$this->data = strtoupper($data);
			$this->properties = $properties;
		}

		/**
		 * This method renders the barcode as a string.
		 *
		 * @access public
		 * @return mixed                                            the barcode
		 */
		public function render() {
			if ($this->image === null) {
				ob_start();

				$showText = (isset($this->properties['showText']) && $this->properties['showText']);

				// Generates the barcode image
				$data = '*' . $this->data . '*';
				$length = strlen($data);
				$width = $length * 16;
				$height = 33;
				$padding = array( // Follows CSS padding rules: top right bottom left
					0, // top
					0, // right
					($showText) ? 18 : 0, // bottom
					0, // left
				);
				$image = imagecreate(($padding[1] + $padding[3]) + $width, ($padding[0] + $padding[2]) + $height);
				$color = 0;
				$black = imagecolorallocate($image, 0, 0, 0);
				$white = imagecolorallocate($image, 255, 255, 255);
				imagefilledrectangle($image, 0, 0, ($padding[1] + $padding[3]) + $width, ($padding[0] + $padding[2]) + $height, $white);
				$x1 = 0;
				$x2 = 0;
				for ($i = 0; $i < $length; $i++) {
					$code = self::$values[$data[$i]];
					for ($j = 0; $j < 9; $j++) {
						switch ($code[$j]) { // symbol
							case 'B': // wide
								$x2 = $x1 + 2;
								$color = $black;
								break;
							case 'b': // narrow
								$x2 = $x1 + 0;
								$color = $black;
								break;
							case 'W': // wide
								$x2 = $x1 + 2;
								$color = $white;
								break;
							case 'w': // narrow
								$x2 = $x1 + 0;
								$color = $white;
								break;
						}
						imagefilledrectangle($image, $padding[3] + $x1, $padding[0], $padding[3] + $x2, $padding[0] + $height, $color);
						$x1 = $x2 + 1;
					}
					$x1 += 1;
				}

				// Adds the human readable label
				if ($showText) {
					$offset = array(16, 1); // x, y
					$adjustment = 5;
					$font = 5;
					$length = strlen($this->data);
					for ($x = 1; $x <= $length; $x++) {
						imagestring($image, $font, $padding[3] + ($x * $offset[0]) + $adjustment, $padding[0] + $height + $offset[1], $data[$x], $black);
					}
				}

				// Outputs image
				imagepng($image);
				imagedestroy($image);

				$this->image = ob_get_clean();
			}
			return $this->image;
		}

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This function acts as a lookup table for matching Code 39 patterns.
		 *
		 * @access protected
		 * @static
		 * @return array                                            the lookup table
		 */
		protected static $patterns = array(
			'bwbWBwBwb' => '0',
			'BwbWbwbwB' => '1',
			'bwBWbwbwB' => '2',
			'BwBWbwbwb' => '3',
			'bwbWBwbwB' => '4',
			'BwbWBwbwb' => '5',
			'bwBWBwbwb' => '6',
			'bwbWbwBwB' => '7',
			'BwbWbwBwb' => '8',
			'bwBWbwBwb' => '9',
			'BwbwbWbwB' => 'A',
			'bwBwbWbwB' => 'B',
			'BwBwbWbwb' => 'C',
			'bwbwBWbwB' => 'D',
			'BwbwBWbwb' => 'E',
			'bwBwBWbwb' => 'F',
			'bwbwbWBwB' => 'G',
			'BwbwbWBwb' => 'H',
			'bwBwbWBwb' => 'I',
			'bwbwBWBwb' => 'J',
			'BwbwbwbWB' => 'K',
			'bwBwbwbWB' => 'L',
			'BwBwbwbWb' => 'M',
			'bwbwBwbWB' => 'N',
			'BwbwBwbWb' => 'O',
			'bwBwBwbWb' => 'P',
			'bwbwbwBWB' => 'Q',
			'BwbwbwBWb' => 'R',
			'bwBwbwBWb' => 'S',
			'bwbwBwBWb' => 'T',
			'BWbwbwbwB' => 'U',
			'bWBwbwbwB' => 'V',
			'BWBwbwbwb' => 'W',
			'bWbwBwbwB' => 'X',
			'BWbwBwbwb' => 'Y',
			'bWBwBwbwb' => 'Z',
			'bWbwbwBwB' => '-',
			'BWbwbwBwb' => '.',
			'bWBwbwBwb' => ' ',
			'bWbWbWbwb' => '$',
			'bWbWbwbWb' => '/',
			'bWbwbWbWb' => '+',
			'bwbWbWbWb' => '%',
			'bWbwBwBwb' => '*',
		);

		/**
		 * This function acts as a lookup table for matching Code 39 code sets.
		 *
		 * @access protected
		 * @static
		 * @return array                                            the lookup table
		 */
		protected static $values = array(
			'0' => 'bwbWBwBwb',
			'1' => 'BwbWbwbwB',
			'2' => 'bwBWbwbwB',
			'3' => 'BwBWbwbwb',
			'4' => 'bwbWBwbwB',
			'5' => 'BwbWBwbwb',
			'6' => 'bwBWBwbwb',
			'7' => 'bwbWbwBwB',
			'8' => 'BwbWbwBwb',
			'9' => 'bwBWbwBwb',
			'A' => 'BwbwbWbwB',
			'B' => 'bwBwbWbwB',
			'C' => 'BwBwbWbwb',
			'D' => 'bwbwBWbwB',
			'E' => 'BwbwBWbwb',
			'F' => 'bwBwBWbwb',
			'G' => 'bwbwbWBwB',
			'H' => 'BwbwbWBwb',
			'I' => 'bwBwbWBwb',
			'J' => 'bwbwBWBwb',
			'K' => 'BwbwbwbWB',
			'L' => 'bwBwbwbWB',
			'M' => 'BwBwbwbWb',
			'N' => 'bwbwBwbWB',
			'O' => 'BwbwBwbWb',
			'P' => 'bwBwBwbWb',
			'Q' => 'bwbwbwBWB',
			'R' => 'BwbwbwBWb',
			'S' => 'bwBwbwBWb',
			'T' => 'bwbwBwBWb',
			'U' => 'BWbwbwbwB',
			'V' => 'bWBwbwbwB',
			'W' => 'BWBwbwbwb',
			'X' => 'bWbwBwbwB',
			'Y' => 'BWbwBwbwb',
			'Z' => 'bWBwBwbwb',
			'-' => 'bWbwbwBwB',
			'.' => 'BWbwbwBwb',
			' ' => 'bWBwbwBwb',
			'$' => 'bWbWbWbwb',
			'/' => 'bWbWbwbWb',
			'+' => 'bWbwbWbWb',
			'%' => 'bwbWbWbWb',
			'*' => 'bWbwBwBwb',
		);

		/**
		 * This function computes the checksum for the specified data string.
		 *
		 * @access public
		 * @static
		 * @param $data string                                      the data string to be evaluated
		 * @return mixed                                            the checksum for the specified data string
		 * @see http://en.wikipedia.org/wiki/Code_39
		 */
		public static function checksum($data) {
			$checksum = 0;
			$length = strlen($data);
			$charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';
			for ($i = 0; $i < $length; $i++) {
				$checksum += strpos($charset, $data[$i]);
			}
			return substr($charset, ($checksum % 43), 1);
		}

		/**
		 * This function will read an image with a Code 39 Barcode and will decode it.
		 *
		 * @access public
		 * @static
		 * @param IO\File $file                                     the image's URI
		 * @return string                                           the decode value
		 */
		public static function decode(IO\File $file) {
			$image = NULL;
			$ext = $file->getFileExtension();

			switch ($ext) {
				case 'png':
					$image = imagecreatefrompng($file);
					break;
				case 'jpg':
					$image = imagecreatefromjpeg($file);
					break;
				default:
					throw new Throwable\InvalidArgument\Exception('Unrecognized file format. File name must have either a png or jpg extension.', array('file' => $file));
			}

			$width = imagesx($image);
			$height = imagesy($image);

			$y = floor($height / 2); // finds middle

			$pixels = array();

			for ($x = 0; $x < $width; $x++) {
				$rgb = imagecolorat($image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$pixels[$x] = ((($r + $g + $b) / 3) < 128.0) ? 1 : 0;
			}

			$code = array();
			$bw = array();

			$i = 0;

			$code[0] = 1;
			$bw[0] = 'b';

			for ($x = 1; $x < $width; $x++) {
				if ($pixels[$x] == $pixels[$x - 1]) {
					$code[$i]++;
				}
				else {
					$code[++$i] = 1;
					$bw[$i] = ($pixels[$x] == 1) ? 'b' : 'w';
				}
			}

			$max = 0;

			for ($x = 1; $x < (count($code) - 1); $x++) {
				if ($code[$x] > $max) {
					$max = $code[$x];
				}
			}

			$code_string = '';

			for ($x = 1; $x < (count($code) - 1); $x++) {
				$code_string .= ($code[$x] > ($max / 2) * 1.5) ? strtoupper($bw[$x]) : $bw[$x];
			}

			// parse code string
			$msg = '';

			for ($x = 0; $x < strlen($code_string); $x += 10) {
				$msg .= self::$patterns[substr($code_string, $x, 9)];
			}

			return $msg;
		}

	}

}
