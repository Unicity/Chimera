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

namespace Unicity\Config\JSON {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService;

	class Helper extends Core\Object {

		public static function buffer($collection) : IO\File {
			if ($collection instanceof \JsonSerializable) {
				return new IO\StringRef(json_encode($collection));
			}
			if ($collection instanceof IO\FIle) {
				return $collection;
			}
			if (Common\StringRef::isTypeOf($collection)) {
				return new IO\StringRef(Core\Convert::toString($collection));
			}
			return new IO\StringRef((new Config\JSON\Writer($collection))->render());
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
				return json_encode((object) array());
			}

			return json_encode(
				call_user_func_array('array_merge', $args)
			);
		}

		public static function decode($data) {
			if ($data instanceof \JsonSerializable) {
				$data = new IO\StringRef(json_encode($data));
			}
			if ($data instanceof Common\Collection) {
				return $data;
			}
			if (Common\StringRef::isTypeOf($data)) {
				$data = new IO\StringRef(Core\Convert::toString($data));
			}
			return MappingService\Data\Model\Marshaller::unmarshal(
				Config\JSON\Reader::load($data)
			);
		}

		public static function encode($collection) : string {
			if ($collection instanceof \JsonSerializable) {
				return json_encode($collection);
			}
			if ($collection instanceof IO\FIle) {
				return $collection->getBytes();
			}
			if (Common\StringRef::isTypeOf($collection)) {
				return Core\Convert::toString($collection);
			}
			return (new Config\JSON\Writer($collection))->render();
		}

	}

}