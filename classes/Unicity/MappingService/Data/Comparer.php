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

namespace Unicity\MappingService\Data {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Log;
	use \Unicity\MappingService;
	use \Unicity\Throwable;

	/**
	 * This class is used to compare two sets of models.
	 *
	 * @access public
	 * @class
	 * @package MappingService
	 */
	class Comparer extends Core\Object {

		/**
		 * This variable stores any metadata associated with the comparer.
		 *
		 * @access protected
		 * @var Common\HashSet
		 */
		protected $ignorables;

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 */
		public function __construct(Common\HashSet $ignorables) {
			$this->ignorables = $ignorables; // paths to ignore
		}

		/**
		 * This method returns a log of all changes between the two sets of models.
		 *
		 * @access public
		 * @param Common\IMap $source                               the source models to be evaluated
		 * @param Common\IMap $target                               the target models to be evaluated
		 * @return Common\Mutable\ArrayList                         a log contain any differents between
		 *                                                          the source models and the target models
		 */
		public function compare(Common\IMap $source, Common\IMap $target) {
			$log = new Common\Mutable\ArrayList();
			$this->compareMaps(static::useCollections($source, true), static::useCollections($target, true), '', $log);
			return $log;
		}

		/**
		 * This method logs any changes between the source and target objects.
		 *
		 * @access protected
		 * @param Common\IList $source                              the source object to be evaluated
		 * @param Common\IList $target                              the target object to be evaluated
		 * @param string $path                                      the current path
		 * @param Common\Mutable\IList $log                         a reference to the log
		 */
		protected function compareLists(Common\IList $source, Common\IList $target, $path, Common\Mutable\IList $log) {
			foreach ($source as $index => $source_value) {
				$new_path = static::buildPath($path, $index);
				if ($this->doLog($new_path)) {
					if ($target->hasIndex($index)) {
						$target_value = $target->getValue($index);
						if (($source_value instanceof Common\IList) && ($target_value instanceof Common\IList)) {
							$this->compareLists($source_value, $target_value, $new_path, $log);
						}
						else if (($source_value instanceof Common\IMap) && ($target_value instanceof Common\IMap)) {
							$this->compareMaps($source_value, $target_value, $new_path, $log);
						}
						else {
							$this->compareValues($source_value, $target_value, $new_path, $log);
						}
					}
					else {
						$log->addValue(array(
							'body' => strtr('Target index ":index" is missing in list.', array(
								':index' => $index,
							)),
							'level' => Log\Level::warning()->__name(),
							'path' => $new_path,
							'time' => date('c'),
						));
					}
				}
			}
		}

		/**
		 * This method logs any changes between the source and target objects.
		 *
		 * @access protected
		 * @param Common\IMap $source                               the source object to be evaluated
		 * @param Common\IMap $target                               the target object to be evaluated
		 * @param string $path                                      the current path
		 * @param Common\Mutable\IList $log                         a reference to the log
		 */
		protected function compareMaps(Common\IMap $source, Common\IMap $target, $path, Common\Mutable\IList $log) {
			foreach ($source as $key => $source_value) {
				$new_path = static::buildPath($path, $key);
				if ($this->doLog($new_path)) {
					if ($target->hasKey($key)) {
						$target_value = $target->getValue($key);
						if (($source_value instanceof Common\IList) && ($target_value instanceof Common\IList)) {
							$this->compareLists($source_value, $target_value, $new_path, $log);
						}
						else if (($source_value instanceof Common\IMap) && ($target_value instanceof Common\IMap)) {
							$this->compareMaps($source_value, $target_value, $new_path, $log);
						}
						else {
							$this->compareValues($source_value, $target_value, $new_path, $log);
						}
					}
					else {
						$log->addValue(array(
							'body' => strtr('Target key ":key" is missing in map.', array(
								':key' => $key,
							)),
							'level' => Log\Level::warning()->__name(),
							'path' => $new_path,
							'time' => date('c'),
						));
					}
				}
			}
		}

		/**
		 * This method logs any changes between the source and target values.
		 *
		 * @access protected
		 * @param mixed $source                                     the source value to be evaluated
		 * @param mixed $target                                     the target value to be evaluated
		 * @param string $path                                      the current path
		 * @param Common\Mutable\IList $log                         a reference to the log
		 */
		protected function compareValues($source, $target, $path, Common\Mutable\IList $log) {
			$source_info = Core\DataType::info($source);
			$target_info = Core\DataType::info($target);

			if ($source_info->hash != $target_info->hash) {
				if ($source_info->type != $target_info->type) {
					$log->addValue(array(
						'body' => strtr('Target value is of ":target" type, but source value is of ":source" type.', array(
							':source' => $source_info->type,
							':target' => $target_info->type,
						)),
						'level' => Log\Level::warning()->__name(),
						'path' => $path,
						'time' => date('c'),
					));
				}
				else {
					$log->addValue(array(
						//'body' => 'Target value is different from source value.',
						'body' => strtr('Target value ":target" is different from source value ":source".', array(
							':source' => Core\Convert::toString($source),
							':target' => Core\Convert::toString($target),
						)),
						'level' => Log\Level::warning()->__name(),
						'path' => $path,
						'time' => date('c'),
					));
				}
			}
		}

		/**
		 * This method returns whether a path should be logged.
		 *
		 * @access protected
		 * @param string $path                                      the path to be evaluated
		 * @return boolean                                          whether the path should be logged
		 */
		protected function doLog($path) {
			return (!$this->ignorables->hasValue($path) && !$this->ignorables->hasValue(static::buildPathUsingWildcards($path)));
		}

		/**
		 * This method appends the path with the specified name.
		 *
		 * @access protected
		 * @static
		 * @param string $path                                      the path to be appended
		 * @param mixed $name                                       the name of the next level
		 * @return string                                           a new path with name of next level
		 *                                                          appended
		 */
		protected static function buildPath($path, $name) {
			if (empty($path)) {
				return Core\Convert::toString($name);
			}
			return $path . '.' . Core\Convert::toString($name);
		}

		/**
		 * This method converts array indexes in a path with a wildcard.
		 *
		 * @access protected
		 * @static
		 * @param string $path                                      the path to be converted
		 * @return string                                           the path with array index changed
		 *                                                          to wildcards
		 */
		protected static function buildPathUsingWildcards($path) {
			return preg_replace('/\.(0|[1-9][0-9]*)$/', '.*', preg_replace('/\.(0|[1-9][0-9]*)\./', '.*.', $path));
		}

		/**
		 * This method converts a collection to use collections.
		 *
		 * @access protected
		 * @static
		 * @param mixed $data                                       the data to be converted
		 * @param boolean $case_sensitive                           whether keys are to be case sensitive
		 * @return mixed                                            the converted data
		 */
		protected static function useCollections($data, $case_sensitive) {
			if (is_object($data)) {
				if (($data instanceof Common\IList) || ($data instanceof Common\ISet)) {
					$buffer = new MappingService\Data\Model\Dynamic\ArrayList(null, $case_sensitive);
					foreach ($data as $value) {
						$buffer->addValue(static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else if ($data instanceof Common\IMap) {
					$buffer = new MappingService\Data\Model\Dynamic\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else if (($data instanceof MappingService\Data\IModel) || ($data instanceof \stdClass)) {
					$data = get_object_vars($data);
					$buffer = new MappingService\Data\Model\Dynamic\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
			}
			if (is_array($data)) {
				if (Common\Collection::isDictionary($data)) {
					$buffer = new MappingService\Data\Model\Dynamic\HashMap(null, $case_sensitive);
					foreach ($data as $key => $value) {
						$buffer->putEntry($key, static::useCollections($value, $case_sensitive));
					}
					return $buffer;
				}
				else {
					$buffer = new MappingService\Data\Model\Dynamic\ArrayList(null, $case_sensitive);
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