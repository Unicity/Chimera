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

namespace Unicity\Config\PDF {

	include_once(implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', '..', 'mPDF', 'mpdf.php')));

	use \Unicity\Config;
	use \Unicity\Core;

	/**
	 * This class is used to write a collection to an XML file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\HTML\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'declaration' => true,
				'ext' => '.pdf',
				'memory_limit' => '64M',
				'mime' => 'application/pdf',
				'minify' => array(),
				'template' => '',
				'url' => null,
			);
		}

		/**
		 * This method displays the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function display(Core\IMessage $message = null) {
			ini_set('memory_limit', $this->metadata['memory_limit']);

			$buffer = $this->render();

			$mpdf = new \mPDF();
			$mpdf->WriteHTML($buffer, 0);
			$mpdf->Output();
			exit();
		}

		/**
		 * This method exports the data.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function export(Core\IMessage $message = null) {
			ini_set('memory_limit', $this->metadata['memory_limit']);

			if (isset($this->metadata['uri']) && !empty($this->metadata['uri'])) {
				$uri = preg_split('!(\?.*|/)!', $this->metadata['uri'], -1, PREG_SPLIT_NO_EMPTY);
				$uri = $uri[count($uri) - 1];
			}
			else {
				$uri = date('YmdHis') . $this->metadata['ext'];
			}

			$buffer = $this->render();

			$mpdf = new \mPDF();
			$mpdf->WriteHTML($buffer, 0);
			$mpdf->Output($uri, 'D');
			exit();
		}

		/**
		 * This method saves the data to disk.
		 *
		 * @access public
		 */
		public function save() {
			ini_set('memory_limit', $this->metadata['memory_limit']);

			if (!isset($this->metadata['uri']) || empty($this->metadata['uri'])) {
				date_default_timezone_set('America/Denver');
				$this->metadata['uri'] = date('YmdHis') . $this->metadata['ext'];
			}

			$uri = $this->metadata['uri'];
			$buffer = $this->render();

			$mpdf = new \mPDF();
			$mpdf->WriteHTML($buffer, 0);
			$mpdf->Output($uri, 'F');
		}

	}

}