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

namespace Unicity\ORM\Dynamic\Model {

	use \Unicity\Bootstrap;
	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Locale;
	use \Unicity\ORM;

	class Marshaller {

		/**
		 * This method returns the localized version of model.
		 *
		 * @access public
		 * @static
		 * @param ORM\IModel $source                                the source model to be localized
		 * @param string $type                                      the data type of the source model
		 * @return ORM\IModel                                       the target model
		 */
		public static function localize(ORM\IModel $source, $type) {
			$type = Core\Convert::toString($type);

			$components = preg_split('/(\\\|_)+/', trim($type, '\\'));

			$languages = Locale\Info::getLanguages();

			foreach ($languages as $language => $q) {
				$ext = '_' . str_replace('-', '_', $language) . '.properties';

				$fileName = implode(DIRECTORY_SEPARATOR, $components) . $ext;

				foreach (Bootstrap::$classpaths as $directory) {
					$uri = Bootstrap::rootPath() . $directory . $fileName;

					if (file_exists($uri)) {
						$properties = Config\Properties\Reader::load(new IO\File($uri))->read();
						return static::useLocalization($source, $properties, '');
					}
					$uri = $directory . $fileName;
					if (file_exists($uri)) {
						$properties = Config\Properties\Reader::load(new IO\File($uri))->read();
						return static::useLocalization($source, $properties, '');
					}
				}
			}

			return $source;
		}

		/**
		 * This method loads the specified file for reading.
		 *
		 * @access public
		 * @static
		 * @param Config\Reader $reader                             the config reader to use
		 * @param array $policy                                     the policy for reading in the data
		 * @return ORM\IModel                                       the model
		 */
		public static function unmarshal(Config\Reader $reader, array $policy = array()) {
			$case_sensitive = isset($policy['case_sensitive'])
				? Core\Convert::toBoolean($policy['case_sensitive'])
				: true;
			$path = isset($policy['path']) ? $policy['path'] : null;
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
		private static function useCollections($data, bool $case_sensitive) {
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

		/**
		 * This method maps the localized keys to the model.
		 *
		 * @access private
		 * @static
		 * @param mixed $source                                     the data to be localized
		 * @param Common\IMap $properties                           the localization mappings
		 * @param string $path                                      the current path
		 * @return array|ArrayList|HashMap                          the data after being localized
		 */
		private static function useLocalization(/*mixed*/ $source, Common\IMap $properties, string $path) {
			if (is_object($source)) {
				if (($source instanceof Common\IList) || ($source instanceof Common\ISet)) {
					$buffer = new ORM\Dynamic\Model\ArrayList(null, true);
					$id = ORM\Query::path($path, '*');
					foreach ($source as $value) {
						$buffer->addValue(static::useLocalization($value, $properties, $id));
					}
					return $buffer;
				}
				else if ($source instanceof Common\IMap) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($source as $key => $value) {
						$id = ORM\Query::path($path, $key);
						if ($properties->hasKey($id)) {
							$buffer->putEntry($properties->getValue($id), static::useLocalization($value, $properties, $id));
						}
						else {
							$buffer->putEntry($key, static::useLocalization($value, $properties, $id));
						}
					}
					return $buffer;
				}
				else if ($source instanceof \stdClass) {
					$source = get_object_vars($source);
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($source as $key => $value) {
						$id = ORM\Query::path($path, $key);
						if ($properties->hasKey($id)) {
							$buffer->putEntry($properties->getValue($id), static::useLocalization($value, $properties, $id));
						}
						else {
							$buffer->putEntry($key, static::useLocalization($value, $properties, $id));
						}
					}
					return $buffer;
				}
			}
			if (is_array($source)) {
				if (Common\Collection::isDictionary($source)) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($source as $key => $value) {
						$id = ORM\Query::path($path, $key);
						if ($properties->hasKey($id)) {
							$buffer->putEntry($properties->getValue($id), static::useLocalization($value, $properties, $id));
						}
						else {
							$buffer->putEntry($key, static::useLocalization($value, $properties, $id));
						}
					}
					return $buffer;
				}
				else {
					$buffer = new ORM\Dynamic\Model\ArrayList(null, true);
					$id = ORM\Query::path($path, '*');
					foreach ($source as $value) {
						$buffer->addValue(static::useLocalization($value, $properties, $id));
					}
					return $buffer;
				}
			}
			return $source;
		}

	}

}