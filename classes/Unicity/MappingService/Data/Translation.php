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

namespace Unicity\MappingService\Data {

	use \Unicity\Core;
	use \Unicity\MappingService;

	/**
	 * This class represents a data translation that will handle the transformation between
	 * the canonical format and the model's format.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package MappingService
	 */
	abstract class Translation extends Core\AbstractObject {

		/**
		 * This variable stores the data that will be transformed.
		 *
		 * @access protected
		 * @var \Unicity\MappingService\Data\Field                  the data that will be transformed
		 */
		protected $field;

		/**
		 * This variable stores the metadata associated with the translation.
		 *
		 * @access protected
		 * @var \Unicity\MappingService\Data\Metadata               the metadata associated with the
		 *                                                          translation
		 */
		protected $metadata;

		/**
		 * This constructor initializes the class with the data field and the metadata associated
		 * with the translation.
		 *
		 * @access public
		 * @param \Unicity\MappingService\Data\Field $field         the data that will be transformed
		 * @param \Unicity\MappingService\Data\Metadata $metadata   the metadata associated with the
		 *                                                          translation
		 */
		public function __construct(MappingService\Data\Field $field, MappingService\Data\Metadata $metadata = null) {
			$this->field = $field;
			$this->metadata = $metadata;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->field);
			unset($this->metadata);
		}

		/**
		 * This method returns the metadata associated with the translation.
		 *
		 * @access public
		 * @return \Unicity\MappingService\Data\Metadata            the metadata associated with the
		 *                                                          translation
		 */
		public function getMetadata() : MappingService\Data\Metadata {
			if ($this->metadata === null) {
				$this->metadata = new MappingService\Data\Metadata();
			}
			return $this->metadata;
		}

		/**
		 * This method returns the data in the canonical format and will handle the transformation
		 * if necessary.
		 *
		 * @access public
		 * @return \Unicity\MappingService\Data\Field               the data in the canonical format
		 */
		public function toCanonicalFormat() : MappingService\Data\Field {
			$field = new MappingService\Data\Field(MappingService\Data\FormatType::canonical(), $this->field);
			$field->setInfo($this->field->getInfo());
			return $field;
		}

		/**
		 * This method returns the data in the model's format and will handle the transformation
		 * if necessary.
		 *
		 * @access public
		 * @return \Unicity\MappingService\Data\Field               the data in the model's format
		 */
		public function toModelFormat() : MappingService\Data\Field {
			$field = new MappingService\Data\Field(MappingService\Data\FormatType::model(), $this->field);
			$field->setInfo($this->field->getInfo());
			return $field;
		}

		/**
		 * This method creates a new instance of this class with the data field and the metadata
		 * associated with the translation.
		 *
		 * @access public
		 * @param \Unicity\MappingService\Data\Field $field         the data that will be transformed
		 * @param \Unicity\MappingService\Data\Metadata $metadata   the metadata associated with the
		 *                                                          translation
		 * @return \Unicity\MappingService\Data\Translation         a new instance of this class
		 */
		public static function factory(MappingService\Data\Field $field, MappingService\Data\Metadata $metadata = null) : MappingService\Data\Translation {
			return new static($field, $metadata);
		}

		/**
		 * This method returns the field for the specified data format.
		 *
		 * @access public
		 * @static
		 * @param MappingService\Data\Field $field                  the data field to store the items
		 * @param Core\Data\XML $translation                        the translation to be parsed
		 */
		public static function translate(MappingService\Data\Field $field, Core\Data\XML $translation) {
			$format = $field->getFormatType()->__name();
			$nodes = $translation->xpath("./Field[@Format='{$format}']");
			if (!empty($nodes)) {
				$children = $nodes[0]->children();
				if (count($children) > 0) {
					foreach ($children as $child) {
						$name = $child->getName();
						if ($name == 'Item') {
							$attributes = $child->attributes();
							if (isset($attributes['Name'])) {
								$key = Core\Data\XML::valueOf($attributes['Name']);
								$value = dom_import_simplexml($child[0])->textContent;
								if (isset($attributes['Type'])) {
									$type = Core\Data\XML::valueOf($attributes['Type']);
									if (!(is_string($type) && preg_match('/^(bool(ean)?|int(eger)?|float|string)$/i', $type))) {
										$type = 'string';
									}
									settype($value, $type);
								}
								else {
									settype($value, 'string');
								}
								$field->putItem($key, $value);
							}
						}
					}
				}
			}
		}

	}

}