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

namespace Unicity\Minify {

	use \Unicity\Core;

	/**
	 * This class will minify an XML file by removing unnecessary content according
	 * to selected options.
	 */
	class XML extends Core\Object {

		/**
		 * This variable stores the XML data being processed.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $xml;

		/**
		 * This variable stores which options to use when processing the XML.
		 *
		 * @access protected
		 * @var array
		 */
		protected $options;

		/**
		 * "Minify" XML
		 *
		 * @access public
		 * @static
		 * @param string $xml
		 * @param array $options
		 *
		 * 'preserveComments' : boolean - (optional) whether to preserve (i.e. not remove) comments
		 * 'preserveEmptyNodes' : boolean - (optional) whether to preserve (i.e. not remove) empty nodes
		 * 'preserveWhiteSpace' : boolean - (optional) whether to preserve (i.e. not remove) redundant whitespace
		 *
		 * @return string
		 */
		public static function minify($xml, $options = array()) {
			$minifier = new static($xml, $options);
			return $minifier->process();
		}

		/**
		 * Create a minifier object
		 *
		 * @access public
		 * @param string $xml
		 * @param array $options
		 */
		public function __construct($xml, array $options = array()) {
			$this->xml = str_replace("\r\n", "\n", trim($xml));
			$this->options = array_merge(array(
				'preserveComments' => true,
				'preserveEmptyLines' => true,
				'preserveEmptyNodes' => true,
				'preserveWhiteSpace' => true,
			), $options);
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->xml);
			unset($this->options);
		}

		/**
		 * Minify the markeup given in the constructor
		 *
		 * @return string
		 */
		public function process() {
			$xml = $this->xml;

			$document = new \DOMDocument();

			if (!$this->options['preserveWhiteSpace']) {
				$document->preserveWhiteSpace = false;
			}
			else {
				$xml = preg_replace('/ +/', ' ', $xml);
				$xml = preg_replace('/ +(\\R)+/', "\n", $xml);
			}

			$document->loadXML($xml);

			$xpath = new \DOMXPath($document);

			// http://www.meetup.com/sf-php/messages/boards/thread/9078171
			if (!$this->options['preserveComments']) {
				while (($nodes = $xpath->query('//comment()')) && $nodes->length) {
					foreach ($nodes as $node) {
						$node->parentNode->removeChild($node);
					}
				}
			}

			// http://stackoverflow.com/questions/8603237/remove-empty-tags-from-a-xml-with-php
			// not(*) does not have children elements
			// not(@*) does not have attributes
			// text()[normalize-space()] nodes that include whitespace text
			if (!$this->options['preserveEmptyNodes']) {
				while (($nodes = $xpath->query('//*[not(*) and not(@*) and not(text()[normalize-space()])]')) && $nodes->length) {
					foreach ($nodes as $node) {
						$node->parentNode->removeChild($node);
					}
				}
			}

			$xml = $document->saveXML();

			if (!$this->options['preserveEmptyLines']) {
				$lines = preg_split('/\\R/', $xml);
				$lines = array_filter($lines, function(string $line) {
					return (trim($line) != '');
				});
				$xml = implode("\n", $lines);
				unset($lines);
			}

			return $xml;
		}

	}

}
