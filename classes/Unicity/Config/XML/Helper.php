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

namespace Unicity\Config\XML {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\MappingService;

	class Helper extends Core\AbstractObject {

		public static function decode($data, array $metadata = array(), bool $assoc = false) /* array|object */{
			$buffer = static::unmarshal($data, $metadata);
			if ($assoc) {
				return Common\Collection::useArrays($buffer);
			}
			return Common\Collection::useObjects($buffer);
		}

		public static function encode($collection, array $metadata = array()) : string {
			if ($collection instanceof \JsonSerializable) {
				$collection = json_decode(json_encode($collection));
			}
			if ($collection instanceof IO\FIle) {
				return $collection->getBytes();
			}
			if (Common\StringRef::isTypeOf($collection)) {
				return Core\Convert::toString($collection);
			}
			return (new Config\XML\Writer($collection))
				->config(array_merge(['declaration' => false], $metadata))
				->render();
		}

		public static function marshal($collection, array $metadata = array()) : IO\File {
			return new IO\StringRef(static::encode($collection, $metadata));
		}

		public static function unmarshal($data, array $metadata = array()) /* list|map */{
			if (is_array($data) || ($data instanceof \JsonSerializable)) {
				$data = new IO\StringRef(static::encode($data));
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
				Config\XML\Reader::load($data, $metadata)
			);
		}

	}

}