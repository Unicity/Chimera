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

namespace Unicity\Barcode {

	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class is used to create a barcode.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Barcode
	 */
	abstract class Creator extends Core\AbstractObject {

		/**
		 * This variable stores the barcode data.
		 *
		 * @access protected
		 * @var string
		 */
		protected $data;

		/**
		 * This variable stores the barcode's file information.
		 *
		 * @access protected
		 * @var IO\File
		 */
		protected $file;

		/**
		 * This variable stores the barcode image.
		 *
		 * @access protected
		 * @var string
		 */
		protected $image;

		/**
		 * This variable stores the barcode's encoded URI.
		 *
		 * @access protected
		 * @var string
		 */
		protected $uri;

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->data);
			unset($this->file);
			unset($this->image);
			unset($this->uri);
		}

		/**
		 * This method displays the barcode image.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function display(Core\IMessage $message = null) : void {
			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\ImageBuffer($this->render());

			$message->setHeader('content-disposition', 'inline');
			$message->setHeader('content-type', $buffer->getContentType());

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This function exports the barcode image.
		 *
		 * @access public
		 * @param Core\IMessage $message                            the message container
		 */
		public function export(Core\IMessage $message = null) : void {
			$uri = (string) $this->file;

			if ($message === null) {
				$message = new Core\Message();
				$send = true;
			}
			else {
				$send = false;
			}

			$buffer = new IO\ImageBuffer($this->render());

			$message->setHeader('content-disposition', 'attachment; filename="' . $uri . '"');
			$message->setHeader('content-type', $buffer->getContentType());

			$message->setBody($buffer);

			if ($send) {
				$message->send();
			}
		}

		/**
		 * This function renders the HTML image tag for displaying the barcode.
		 *
		 * @access public
		 * @param array $attributes                                 any additional attributes to be added
		 *                                                          to the HTML image tag
		 * @return string                                           the HTML image tag
		 */
		public function html($attributes = array()) {
			$properties = '';
			if (is_array($attributes)) {
				foreach ($attributes as $key => $val) {
					$properties .= "{$key}=\"{$val}\" ";
				}
			}
			$html = '<img src="' . $this->toEncodedURI() . '" ' . $properties . '/>';
			return $html;
		}

		/**
		 * This method returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property
		 * @throws Throwable\InvalidProperty\Exception              indicates that the specified property
		 *                                                          is either inaccessible or undefined
		 */
		public function __get($name) {
			switch ($name) {
				case 'file':
					return $this->file;
				default:
					throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property ":name" is either inaccessible or undefined.', array(':name' => $name));
			}
		}

		/**
		 * This method renders the barcode as a string.
		 *
		 * @access public
		 * @abstract
		 * @return string                                           the barcode
		 */
		public abstract function render();

		/**
		 * This method saves the image of the barcode to disk.
		 *
		 * @access public
		 * @param IO\File $file                                     the URI for where the image
		 *                                                          will be stored
		 */
		public function save(IO\File $file) {
			file_put_contents($file, $this->render());
			$this->file = $file;
		}

		/**
		 * This method returns the barcode in the form of an encoded URI.
		 *
		 * @return string
		 */
		public function toEncodedURI() : string {
			if ($this->uri === null) {
				$this->uri = 'data:image/png;base64,' . urlencode(base64_encode($this->render()));
			}
			return $this->uri;
		}

		/**
		 * This method returns the barcode represented as a string.
		 *
		 * @access public
		 * @return string                                           the barcode as a string
		 */
		public function __toString() {
			return Core\Convert::toString($this->render());
		}

	}

}