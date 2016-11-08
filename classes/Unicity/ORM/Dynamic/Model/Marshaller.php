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
	use \Unicity\FP;
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
						return static::_localize($source, $uri);
					}
					$uri = $directory . $fileName;
					if (file_exists($uri)) {
						return static::_localize($source, $uri);
					}
				}
			}

			return $source;
		}

		/**
		 * This method returns the localized version of model using the property mappings
		 * at the specificed URI.
		 *
		 * @access private
		 * @static
		 * @param ORM\IModel $source                                the source model to be localized
		 * @param string $uri                                       the URI to the property mappings
		 * @return ORM\IModel                                       the target model
		 */
		private static function _localize(ORM\IModel $source, string $uri) {
			$properties = Config\Properties\Reader::load(new IO\File($uri))->read();
			return static::useLocalization($source, $properties, '');
		}

		/**
		 * This method returns the unlocalized version of model.
		 *
		 * @access public
		 * @static
		 * @param ORM\IModel $target                                the target model to be unlocalized
		 * @param string $type                                      the data type of the source model
		 * @return ORM\IModel                                       the source model
		 */
		public static function unlocalize(ORM\IModel $target, $type) {
			$type = Core\Convert::toString($type);

			$components = preg_split('/(\\\|_)+/', trim($type, '\\'));

			$languages = Locale\Info::getLanguages();

			foreach ($languages as $language => $q) {
				$ext = '_' . str_replace('-', '_', $language) . '.properties';

				$fileName = implode(DIRECTORY_SEPARATOR, $components) . $ext;

				foreach (Bootstrap::$classpaths as $directory) {
					$uri = Bootstrap::rootPath() . $directory . $fileName;
					if (file_exists($uri)) {
						return static::_unlocalize($target, $uri);
					}
					$uri = $directory . $fileName;
					if (file_exists($uri)) {
						return static::_unlocalize($target, $uri);
					}
				}
			}

			return $target;
		}

		/**
		 * This method returns the unlocalized version of model using the property mappings
		 * at the specificed URI.
		 *
		 * @access private
		 * @static
		 * @param ORM\IModel $target                                the target model to be unlocalized
		 * @param string $uri                                       the URI to the property mappings
		 * @return ORM\IModel                                       the source model
		 */
		private static function _unlocalize(ORM\IModel $target, string $uri) {
			$properties = Config\Properties\Reader::load(new IO\File($uri))->read();
			$properties = FP\IMap::map($properties, function(Common\Tuple $tuple) {
				$k1 = (string) $tuple->first();
				$k2 = explode('.', $k1);

				$index = count($k2) - 1;

				$v1 = $k2[$index];
				$v2 = (string) $tuple->second();

				$k2[$index] = $v2;
				$k1 = implode('.', $k2);

				return Common\Tuple::box2($k1, $v1);
			});
			return static::useLocalization($target, $properties, '');
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
		 * @param mixed $data                                       the data to be localized
		 * @param Common\IMap $properties                           the localization mappings
		 * @param string $path                                      the current path
		 * @return array|ArrayList|HashMap                          the data after being localized
		 */
		private static function useLocalization(/*mixed*/ $data, Common\IMap $properties, string $path) {
			if (is_object($data)) {
				if (($data instanceof Common\IList) || ($data instanceof Common\ISet)) {
					$buffer = new ORM\Dynamic\Model\ArrayList(null, true);
					$id = ORM\Query::path($path, '*');
					foreach ($data as $value) {
						$buffer->addValue(static::useLocalization($value, $properties, $id));
					}
					return $buffer;
				}
				else if ($data instanceof Common\IMap) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($data as $key => $value) {
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
				else if ($data instanceof \stdClass) {
					$data = get_object_vars($data);
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($data as $key => $value) {
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
			if (is_array($data)) {
				if (Common\Collection::isDictionary($data)) {
					$buffer = new ORM\Dynamic\Model\HashMap(null, true);
					foreach ($data as $key => $value) {
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
					foreach ($data as $value) {
						$buffer->addValue(static::useLocalization($value, $properties, $id));
					}
					return $buffer;
				}
			}
			return $data;
		}

	}

}