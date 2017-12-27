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

namespace Unicity\Config\QueryString {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\HTTP;
	use \Unicity\IO;
	use \Unicity\MappingService;

	class Helper extends Core\Object {

		public static function build($parameters, bool $prefix = true) : string {
			$parameters = Common\Collection::useArrays($parameters);
			if (is_array($parameters)) {
				$query_string = http_build_query($parameters);
				if (!empty($query_string) && $prefix) {
					$query_string = '?' . $query_string;
				}
			}
			return $query_string;
		}

		public static function combine(... $args) : string {
			$args = array_filter($args, function($arg) {
				return is_string($arg) || is_array($arg);
			});

			$args = array_map(function($arg) {
				if (is_array($arg)) {
					return $arg;
				}
				return json_decode($arg, true);
			}, $args);

			if (empty($args)) {
				return '';
			}

			return static::build(
				call_user_func_array('array_merge', $args)
			);
		}

		public static function decode($data, array $metadata = array()) /* array|object */{
			return Common\Collection::useObjects(static::unmarshal($data, $metadata));
		}

		public static function encode($collection, array $metadata = array()) : string {
			if ($collection instanceof \JsonSerializable) {
				return (new Config\QueryString\Writer(json_decode(json_encode($collection))))->render();
			}
			if ($collection instanceof IO\FIle) {
				return $collection->getBytes();
			}
			if (Common\StringRef::isTypeOf($collection)) {
				return Core\Convert::toString($collection);
			}
			return (new Config\QueryString\Writer($collection))->config($metadata)->render();
		}

		public static function marshal($collection, array $metadata = array()) : IO\File {
			return new IO\StringRef(static::encode($collection, $metadata));
		}

		public static function unmarshal($data, array $metadata = array()) /* list|map */{
			if ($data instanceof \JsonSerializable) {
				$data = new IO\StringRef(json_encode($data));
			}
			if ($data instanceof Common\ICollection) {
				if (isset($metadata['encoding'])) {
					$data = \Unicity\Core\Data\Charset::encodeData($data, $metadata['encoding'][0], $metadata['encoding'][0]);
				}
				return $data;
			}
			if (Common\StringRef::isTypeOf($data)) {
				$data = new IO\StringRef(Core\Convert::toString($data));
			}
			return MappingService\Data\Model\Marshaller::unmarshal(
				Config\QueryString\Reader::load($data, $metadata)
			);
		}

	}

}