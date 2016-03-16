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

namespace Unicity\OrderCalc\Engine\Model {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\OrderCalc;

	class Marshaller {

		/**
		 * This method loads the specified file for reading.
		 *
		 * @access public
		 * @static
		 * @param Config\Reader $reader                             the config reader to use
		 * @param array $policy                                     the policy for reading in the data
		 * @return OrderCalc\Engine\IModel                          the model
		 */
		public static function unmarshal(Config\Reader $reader, array $policy = array()) {
			$case_sensitive = isset($policy['case_sensitive'])
				? Core\Convert::toBoolean($policy['case_sensitive'])
				: true;
			$path = isset($policy['path']) ? $policy['path'] : null;
			if (($path !== null) && !$case_sensitive) {
				$path = strtolower($path);
			}
			$schema = OrderCalc\Engine\Model\JSON\Helper::resolveJSONSchema($policy['schema']);
			$type = isset($schema['type']) ? $schema['type'] : 'object';
			switch ($type) {
				case 'array':
					$array = new OrderCalc\Engine\Model\JSON\ArrayList($schema, $case_sensitive);
					$array->addValues($reader->read($path));
					return $array;
				default:
					$object = new OrderCalc\Engine\Model\JSON\HashMap($schema, $case_sensitive);
					$object->putEntries($reader->read($path));
					return $object;
			}
		}

	}

}