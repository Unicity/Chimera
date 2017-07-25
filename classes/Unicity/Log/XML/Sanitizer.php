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

	use \Unicity\Common;
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

		protected $filters;

		public function __construct(IO\File $file) {
			$config = Common\Collection::useCollections(Config\JSON\Reader::load($file)->read());
			$this->filters = array();
			foreach ($config->filters as $filter) {
				$delegate = $filter->hasKey('delegate') ? $filter->delegate : null;
				foreach ($filter->rules as $rule) {
					if ($rule->hasKey('attribute')) {
						$this->filters[] = (object) [
							'attribute' => $rule->attribute,
							'delegate' => $delegate,
							'query' => $rule->query,
						];
					}
					else {
						$this->filters[] = (object) [
							'delegate' => $delegate,
							'query' => $rule->query,
						];
					}
				}
			}
		}

		public function sanitize(IO\File $input, array $metadata = array()) : IO\StringRef {
			$document = new \DOMDocument();
			$document->loadXML($input->getBytes());
			foreach ($this->filters as $filter) {
				$xpath = new \DOMXpath($document);
				$elements = $xpath->query($filter->query);
				if (!empty($elements)) {
					$delegate = $filter->delegate;
					if (is_callable($delegate)) {
						if (isset($filter->attribute)) {
							foreach ($elements as $element) {
								$attributes = $element->attributes;
								$attribute = $attributes->getNamedItem($filter->attribute);
								if ($attribute !== null) {
									$attribute->nodeValue = $delegate($attribute->nodeValue);
								}
							}
						}
						else {
							foreach ($elements as $element) {
								$element->nodeValue = $delegate($element->nodeValue);
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