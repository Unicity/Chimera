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
	use \Unicity\Core;
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

		public function __construct($filters) {
			$filters = Log\Sanitizer::filters($filters);
			$this->filters = array();
			foreach ($filters as $filter) {
				$rule = $filter->hasKey('rule') ? $filter->rule : null;
				foreach ($filter->keys as $key) {
					if ($key->hasKey('attribute')) {
						$this->filters[] = (object) [
							'attribute' => Core\Convert::toString($key->attribute),
							'namespace' => $key->namespace,
							'path' => Core\Convert::toString($key->path),
							'rule' => $rule,
						];
					}
					else {
						$this->filters[] = (object) [
							'namespace' => $key->namespace,
							'path' => Core\Convert::toString($key->path),
							'rule' => $rule,
						];
					}
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$input = static::input($input);
			$document = new \DOMDocument();
			$document->loadXML($input->getBytes());
			foreach ($this->filters as $filter) {
				$xpath = new \DOMXpath($document);
				if (isset($filter->namespace['prefix']) && isset($filter->namespace['uri'])) {
					$xpath->registerNamespace($filter->namespace['prefix'], $filter->namespace['uri']);
				}
				$elements = $xpath->query($filter->path);
				if (!empty($elements)) {
					$rule = $filter->rule;
					if (is_callable($rule)) {
						if (isset($filter->attribute)) {
							foreach ($elements as $element) {
								$attributes = $element->attributes;
								$attribute = $attributes->getNamedItem($filter->attribute);
								if ($attribute !== null) {
									$attribute->nodeValue = $rule($attribute->nodeValue);
								}
							}
						}
						else {
							foreach ($elements as $element) {
								$element->nodeValue = $rule($element->nodeValue);
							}
						}
					}
					else {
						// TODO remove value if filter is null
					}
				}
			}
			$document->formatOutput = true;
			return $document->saveXML();
		}

	}

}