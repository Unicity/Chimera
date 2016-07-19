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

namespace Unicity\Config\Object {

	use \Unicity\Config;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a PHP serialized object file.
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
			), $metadata);
		}

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public function read($path = null) {
			$buffer = file_get_contents($this->file);

			if ($this->metadata['bom']) {
				$buffer = preg_replace('/^' . pack('H*','EFBBBF') . '/', '', $buffer);
			}

			$collection = unserialize($buffer);

			if ($path !== null) {
				$collection = Config\Helper::factory($collection)->getValue($path);
			}

			return $collection;
		}

	}

}