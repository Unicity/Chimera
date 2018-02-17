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

namespace Unicity\Config\XML {

	use \Unicity\Core;
	use \Unicity\Config;

	/**
	 * This class defines the contract for sanitizing messages.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Sanitizer extends Config\Sanitizer {

		protected $filters;

		public function __construct($filters) {
			$filters = static::filters($filters);
			$this->filters = array();
			foreach ($filters as $filter) {
				$rule = $filter->hasKey('rule') ? $filter->rule : null;
				if (is_string($rule) && array_key_exists($rule, static::$rules)) {
					$rule = static::$rules[$rule];
				}
				foreach ($filter->keys as $key) {
					if ($key->hasKey('attribute')) {
						$this->filters[] = (object) [
							'attribute' => Core\Convert::toString($key->attribute),
							'namespace' => $key->namespace, // ['prefix' => '', 'uri' => '']
							'path' => Core\Convert::toString($key->path),
							'rule' => $rule,
						];
					}
					else {
						$this->filters[] = (object) [
							'namespace' => $key->namespace, // ['prefix' => '', 'uri' => '']
							'path' => Core\Convert::toString($key->path),
							'rule' => $rule,
						];
					}
				}
			}
		}

		public function sanitize($input, array $metadata = array()) : string {
			$document = new \DOMDocument();
			$document->loadXML(Config\XML\Helper::encode($input), LIBXML_NOWARNING);
			foreach ($this->filters as $filter) {
				$xpath = new \DOMXpath($document);
				if (isset($filter->namespace['prefix']) && isset($filter->namespace['uri'])) {
					$xpath->registerNamespace($filter->namespace['prefix'], $filter->namespace['uri']);
				}
				$elements = $xpath->query($filter->path);
				if (!empty($elements)) {
					$rule = $filter->rule;
					if (is_string($rule) && preg_match('/^mask_last\(([0-9]+)\)$/', $rule, $matches)) {
						foreach ($elements as $element) {
							$element->nodeValue = Core\Masks::last($element->nodeValue, 'x', $matches[1]);
						}
					}
					else if (is_callable($rule)) {
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
					else { // remove
						if (isset($filter->attribute)) {
							foreach ($elements as $element) {
								$element->removeAttribute($filter->attribute);
							}
						}
						else {
							$removables = array();
							foreach ($elements as $element) {
								$removables[] = $element;
							}
							foreach ($removables as $element) {
								$element->parentNode->removeChild($element);
							}
						}
					}
				}
			}
			$document->formatOutput = true;
			return $document->saveXML();
		}

	}

}