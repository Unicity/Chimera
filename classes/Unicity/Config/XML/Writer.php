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

namespace Unicity\Config\XML {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Minify;

	/**
	 * This class is used to write a collection to an XML file.
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
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'declaration' => true,
				'encoding' => array(Core\Data\Charset::UTF_8_ENCODING, Core\Data\Charset::UTF_8_ENCODING),
				'eol' => "\n",
				'ext' => '.xml',
				'mime' => 'text/xml',
				'minify' => array(),
				'standalone' => false,
				'template' => '',
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the data as string
		 * @throws \Exception                                       indicates a problem occurred
		 *                                                          when generating the template
		 */
		public function render() {
			$metadata = $this->metadata;
			$declaration = ($metadata['declaration'])
				? Core\Data\XML::declaration($metadata['encoding'][1], $metadata['standalone']) . $metadata['eol']
				: '';
			if (!empty($metadata['template'])) {
				$file = new IO\File($metadata['template']);
				$mustache = new \Mustache_Engine(array(
					'loader' => new \Mustache_Loader_FilesystemLoader($file->getFilePath()),
					'escape' => function($string) use ($metadata) {
						$string = Core\Data\Charset::encode($string, $metadata['encoding'][0], $metadata['encoding'][1]);
						$string = Core\Data\XML::entities($string);
						return $string;
					},
				));
				ob_start();
				try {
					echo $declaration;
					echo $mustache->render($file->getFileName(), $this->data);
				}
				catch (\Exception $ex) {
					ob_end_clean();
					throw $ex;
				}
				$template = ob_get_clean();
				if (!empty($metadata['minify'])) {
					$template = Minify\XML::minify($template, $metadata['minify']);
				}
				return $template;
			}
			else {
				ob_start();
				try {
					$document = new \DOMDocument();
					$document->formatOutput = true;
					$this->toXML($document, $document, $this->data);

					echo $declaration;
					echo $document->saveXML();
				}
				catch (\Exception $ex) {
					ob_end_clean();
					throw $ex;
				}
				$template = ob_get_clean();
				return $template;
			}
		}

		/**
		 * This method returns the data as an XML string.
		 *
		 * @access protected
		 * @param \DOMDocument $document                            the XML DOM document
		 * @param \DOMElement $element                              the XML DOM element
		 * @param mixed $data                                       the data as an XML string
		 */
		protected function toXML($document, $element, $data) {
			if (is_array($data)) {
				if (Common\Collection::isDictionary($data)) {
					foreach ($data as $node => $value) {
						$child = $document->createElement($node);
						$element->appendChild($child);
						$this->toXML($document, $child, $value);
					}
				}
				else {
					foreach ($data as $value) {
						$this->toXML($document, $element, $value);
					}
				}
			}
			else if (is_string($data) && preg_match('/^<!CDATA\[.*\]\]>$/', $data)) {
				$data = substr($data, 8, strlen($data) - 11);
				$child = $document->createCDATASection($data);
				$element->appendChild($child);
			}
			else if ($data !== null) {
				$child = $document->createTextNode(Core\Convert::toString($data));
				$element->appendChild($child);
			}
		}

	}

}