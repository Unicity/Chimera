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

namespace Unicity\ORM\Dynamic\Model {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\ORM;

	class Marshaller {

		/**
		 * This method loads the specified file for reading.
		 *
		 * @access public
		 * @static
		 * @param Config\Reader $reader                             the config reader to use
		 * @param boolean $case_sensitive                           whether keys are to be case sensitive
		 * @param string $path                                      the path to the value to be returned
		 * @return mixed                                            the resource as a collection
		 */
		public static function unmarshal(Config\Reader $reader, $case_sensitive = true, $path = null) {
			if (($path !== null) && !$case_sensitive) {
				$path = strtolower($path);
			}
			return static::useCollections($reader->read($path), Core\Convert::toBoolean($case_sensitive));
		}

		/**
		 * This method converts a collection to use collections.
		 *
		 * @access private
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @param boolean $case_sensitive                           whether keys are to be case sensitive
		 * @return mixed                                            the converted data
		 */
		private static function useCollections($data, $case_sensitive) {
			if (is_object($data)) {
				if (($data instanceof Common\IList) || ($data instanceof Common\ISet)) {
					$buffer = new ORM\Dynamic\Model\ArrayList(null, $case_sensitive);
					foreach ($data as $value) {
						$buffer->addValue(static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else if ($data instanceof Common\IMap) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = new ORM\Dynamic\Model\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
			}
			if (is_array($data)) {
				if (Common\Collection::isDictionary($data)) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else {
					$buffer = new ORM\Dynamic\Model\ArrayList(null, $case_sensitive);
					foreach ($data as $value) {
						$buffer->addValue(static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
			}
			return $data;
		}

	}

}