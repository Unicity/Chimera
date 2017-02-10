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

namespace Unicity\Config\Inc {

	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class is used to build a collection from a PHP-include file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Reader extends Config\Reader {

		/**
		 * This method returns the processed resource as a collection.
		 *
		 * @access public
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public function read($path = null) {
			if ($this->file->getFileSize() > 0) {
				$file = $this->file;
				$reader = function () use ($file) {
					return include($file);
				};
				$collection = $reader();
				if ($path !== null) {
					$path = Core\Convert::toString($path);
					$collection = Config\Helper::factory($collection)->getValue($path);
				}
				return $collection;
			}
			return null;
		}

	}

}