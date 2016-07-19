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

namespace Unicity\ORM\JSON\Model {

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
			$schema = ORM\JSON\Model\Helper::resolveJSONSchema($policy['schema']);
			$type = isset($schema['type']) ? $schema['type'] : 'object';
			switch ($type) {
				case 'array':
					$array = new ORM\JSON\Model\ArrayList($schema, $case_sensitive);
					$array->addValues($reader->read($path));
					return $array;
				default:
					$object = new ORM\JSON\Model\HashMap($schema, $case_sensitive);
					$object->putEntries($reader->read($path));
					return $object;
			}
		}

	}

}