<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2012 Spadefoot Team
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

namespace Unicity\Common\Mutable {

	use \Unicity\Common;
	use \Unicity\Throwable;

	/**
	 * This class creates a mutable bit-field.
	 *
	 * @access public
	 * @class
	 * @package Common
	 */
	class BitField extends Common\BitField {

		/**
		 * This method sets the value for the specified field.
		 *
		 * @access public
		 * @param string $field                                     the name of the field
		 * @param mixed $value                                      the value of the field
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property is
		 *                                                          either inaccessible or undefined
		 */
		public function __set($field, $value) {
			if ( ! array_key_exists($field, $this->values)) {
				throw new Throwable\InvalidProperty\Exception('Unable to set the specified property. Property :field is either inaccessible or undefined.', array(':field' => $field, ':value' => $value));
			}
			$this->values[$field] = bindec(static::unpack($value, $this->boundary));
		}

		/**
		 * This method sets the value for the bit field.
		 *
		 * @access public
		 * @param mixed $value                                      the value of the field
		 * @return BitField                                         a reference to the current instance
		 */
		public function setValue($value) {
			$this->map($value);
			return $this;
		}

	}

}