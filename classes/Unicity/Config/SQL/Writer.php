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

namespace Unicity\Config\SQL {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Spring;

	/**
	 * This class is used to write a collection to an SQL export file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = Common\Collection::useArrays($data);
			$this->metadata = array(
				'builder' => '',
				'command' => 'insert', // 'update'
				'data_source' => 'default',
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'eol' => "\n",
				'ext' => '.sql',
				'mime' => 'text/x-sql',
				'schema' => array(),
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() {
			switch ($this->metadata['command']) {
				case 'update':
					return call_user_func($this->metadata['builder'] . "::toUpdateStatement", $this);
				default:
					return call_user_func($this->metadata['builder'] . "::toInsertStatement", $this);
			}
		}

	}

}