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

	class Helper extends Core\Object {

		public static function decode($data) {
			if ($data instanceof Common\Collection) {
				return $data;
			}
			if (Common\StringRef::isTypeOf($data)) {
				$data = new IO\StringRef(Core\Convert::toString($data));
			}
			return MappingService\Data\Model\Marshaller::unmarshal(
				Config\XML\Reader::load($data)
			);
		}

		public static function encode($collection) : string {
			if ($collection instanceof IO\FIle) {
				return $collection->getBytes();
			}
			if (Common\StringRef::isTypeOf($collection)) {
				return Core\Convert::toString($collection);
			}
			return (new Config\XML\Writer($collection))->render();
		}

	}

}