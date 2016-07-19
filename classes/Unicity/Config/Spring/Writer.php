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

namespace Unicity\Config\Spring {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Minify;
	use \Unicity\Spring;

	/**
	 * This class is used to write a collection to a Spring XML file.
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
			$this->data = Common\Collection::useObjects($data);
			$this->metadata = array(
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'ext' => '.xml',
				'mime' => 'text/xml',
				'minify' => array(),
				'prototype' => true,
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the processed data
		 */
		public function render() : string {
			$object_exporter = new Spring\XMLObjectExporter($this->data);
			$object_exporter->encoding = $this->metadata['encoding'];
			$object_exporter->prototype = $this->metadata['prototype'];
			$spring_xml = $object_exporter->render();
			if (!empty($this->metadata['minify'])) {
				$spring_xml = Minify\XML::minify($spring_xml, $this->metadata['minify']);
			}
			return $spring_xml;
		}

	}

}