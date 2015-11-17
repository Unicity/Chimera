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

namespace Unicity\Config\CSV {

	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Minify;

	/**
	 * This class is used to write a collection to a CSV file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'delimiter' => ',',
				'enclosure' => '"',
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'eol' => "\n",
				'escape' => '\\',
				'ext' => '.csv',
				'header' => true,
				'headings' => array(),
				'mime' => 'text/csv',
				'template' => '',
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the data as string
		 * @throws \Exception                                       indicates a problem occurred
		 *                                                          when generating the template
		 */
		public function render() {
			$delimiter = $this->metadata['delimiter'];
			$enclosure = $this->metadata['enclosure'];
			$escape = $this->metadata['escape'];
			$eol = $this->metadata['eol'];
			$encoding = $this->metadata['encoding'];
			ob_start();
			try {
				if (!empty($this->metadata['template'])) {
					$file = new IO\File($this->metadata['template']);
					$mustache = new \Mustache_Engine(array(
						'loader' => new \Mustache_Loader_FilesystemLoader($file->getFilePath()),
						'escape' => function ($field) use ($delimiter, $enclosure, $escape, $encoding) {
							$value = Core\Data\Charset::encode($field, $encoding[0], $encoding[1]);
							if (($enclosure != '') &&
								((strpos($value, $delimiter) !== false) ||
								(strpos($value, $enclosure) !== false) ||
								(strpos($value, "\n") !== false) ||
								(strpos($value, "\r") !== false) ||
								(strpos($value, "\t") !== false) ||
								(strpos($value, ' ') !== false))) {
								$literal = $enclosure;
								$escaped = 0;
								$length = strlen($value);
								for ($i = 0; $i < $length; $i++) {
									if ($value[$i] == $escape) {
										$escaped = 1;
									}
									else if (!$escaped && $value[$i] == $enclosure) {
										$literal .= $enclosure;
									}
									else {
										$escaped = 0;
									}
									$literal .= $value[$i];
								}
								$literal .= $enclosure;
								return $literal;
							}
							else {
								return Core\Convert::toString($value);
							}
						},
					));
					echo $mustache->render($file->getFileName(), $this->data);
				}
				else {
					if ($this->metadata['header']) {
						if (!empty($this->metadata['headings'])) {
							echo static::format($this->metadata['headings'], $delimiter, $enclosure, $escape, $eol, $encoding);
						}
						else if (!empty($this->data)) {
							echo static::format(array_keys($this->data[0]), $delimiter, $enclosure, $escape, $eol, $encoding);
						}
					}
					foreach ($this->data as $values) {
						echo static::format($values, $delimiter, $enclosure, $escape, $eol, $encoding);
					}
				}
			}
			catch (\Exception $ex) {
				ob_end_clean();
				throw $ex;
			}
			$template = ob_get_clean();
			return $template;
		}

		/**
		 * This method formats an array of values using CSV conventions.
		 *
		 * @access public
		 * @static
		 * @param array $fields                                     the values to be formatted
		 * @param string $delimiter                                 the delimiter to be used
		 * @param string $enclosure                                 the enclosure to be used
		 * @param string $escape                                    the escape character to be used
		 * @param string $eol                                       the end-of-line character to be used
		 * @param array $encoding                                   the character set encoding to be used
		 * @return string                                           the formatted string
		 *
		 * @see http://php.net/manual/en/function.fputcsv.php#77866
		 */
		public static function format(array $fields, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = "\n", array $encoding = null) {
			$buffer = '';
			if (empty($encoding)) {
				$encoding = array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING);
			}
			foreach ($fields as $field) {
				$value = Core\Data\Charset::encode($field, $encoding[0], $encoding[1]);
				if (($enclosure != '') &&
					((strpos($value, $delimiter) !== false) ||
					(strpos($value, $enclosure) !== false) ||
					(strpos($value, "\n") !== false) ||
					(strpos($value, "\r") !== false) ||
					(strpos($value, "\t") !== false) ||
					(strpos($value, ' ') !== false))) {
					$literal = $enclosure;
					$escaped = 0;
					$length = strlen($value);
					for ($i = 0; $i < $length; $i++) {
						if ($value[$i] == $escape) {
							$escaped = 1;
						}
						else if (!$escaped && $value[$i] == $enclosure) {
							$literal .= $enclosure;
						}
						else {
							$escaped = 0;
						}
						$literal .= $value[$i];
					}
					$literal .= $enclosure;
					$buffer .= $literal . $delimiter;
				}
				else {
					$buffer .= Core\Convert::toString($value) . $delimiter;
				}
			}
			$buffer = substr($buffer, 0, -1) . $eol;
			return $buffer;
		}

	}

}