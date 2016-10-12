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

namespace Unicity\Config\Properties {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a Java-style properties file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param IO\File $file                                     the file to be processed
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(IO\File $file, array $metadata = array()) {
			$this->file = $file;
			$this->metadata = array_merge(array(
				'bom' => false, // whether to remove BOM from the first line
				'schema' => array(),
			), $metadata);
		}

		/**
		 * This method iterates over each record in the file, yielding each item to the procedure function.
		 *
		 * @access public
		 * @param callable $procedure                               the procedure function to be used
		 * @throws Throwable\Parse\Exception                        indicates that an invalid record was
		 *                                                          encountered
		 */
		public function each(callable $procedure) {
			$self = $this;

			IO\FileReader::read($this->file, function($reader, $data, $index) use ($self, $procedure) {
				$line = trim($data);
				if (strlen($line) > 0) {
					if (($index == 0) && $self->bom) {
						$line = preg_replace('/^' . pack('H*', 'EFBBBF') . '/', '', $line);
					}
					if (!preg_match('/^\s*#.*$/', $line)) {
						if (!preg_match('/^[^=]+=.+$/', $line)) {
							throw new Throwable\Parse\Exception('Unable to parse file. File ":uri" contains an invalid pattern on line :line.', array(':uri' => $self->file, ':line' => $index));
						}
						$position = strpos($line, '=');
						$key = trim(substr($line, 0, $position));
						$value = trim(substr($line, $position + 1));
						$type = (isset($self->schema[$key])) ? $self->schema[$key] : 'string';
						$value = Core\Convert::changeType($value, $type);

						$record = new Common\Mutable\HashMap();
						$record->putEntry($key, $value);
						$procedure($record);
					}
				}
			});
		}

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 * @throws Throwable\Parse\Exception                        indicates that an invalid record was
		 *                                                          encountered
		 */
		public function read($path = null) {
			if ($this->file->getFileSize() > 0) {
				$collection = new Common\Mutable\HashMap();

				$this->each(function (Common\Mutable\HashMap $record) use ($collection) {
					$collection->putEntries($record);
				});

				if ($path !== null) {
					$collection = Config\Helper::factory($collection)->getValue($path);
				}

				return $collection;
			}
			return null;
		}

	}

}