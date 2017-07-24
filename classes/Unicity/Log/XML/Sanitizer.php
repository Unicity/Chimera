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

namespace Unicity\Log\XML {

	use \Unicity\Config;
	use \Unicity\IO;
	use \Unicity\Log;

	/**
	 * This class defines the contract for sanitizing messages.
	 *
	 * @access public
	 * @class
	 * @package Log
	 */
	class Sanitizer extends Log\Sanitizer {

		protected $entries;

		public function __construct(IO\File $file) {
			$config = Config\JSON\Reader::load($file)->read();
			$this->entries = array();
			foreach ($config['rules'] as $rule) {
				$filter = $rule['filter'];
				foreach ($rule['fields'] as $field) {
					if (isset($field['attribute'])) {
						$this->entries[] = [
							'filter' => $filter,
							'attribute' => $field['attribute'],
							'query' => $field['query'],
						];
					}
					else if (isset($field['element'])) {
						$this->entries[] = [
							'filter' => $filter,
							'element' => $field['element'],
							'query' => $field['query'],
						];
					}
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$document = new \DOMDocument();
			$document->loadXML($input->getBytes());
			foreach ($this->entries as $entry) {
				$xpath = new \DOMXpath($document);
				$elements = $xpath->query($entry['query']);
				if (!empty($elements)) {
					$filter = $entry['filter'];
					if ($filter !== null) {
						if (isset($entry['attribute'])) {
							$attrName = $entry['attribute'];
							foreach ($elements as $element) {
								$attributes = $element->attributes;
								$attribute = $attributes->getNamedItem($attrName);
								if ($attribute !== null) {
									$attribute->nodeValue = $filter($attribute->nodeValue);
								}
							}
						}
						else {
							if (isset($entry['element'])) {
								$nodeName = $entry['element'];
								foreach ($elements as $element) {
									if ($nodeName == $element->nodeName) {
										$element->nodeValue = $filter($element->nodeValue);
									}
								}
							}
						}
					}
					else {
						// TODO remove value if filter is null
					}
				}
			}
			$document->formatOutput = true;
			return new IO\StringRef($document->saveXML());
		}

	}

}