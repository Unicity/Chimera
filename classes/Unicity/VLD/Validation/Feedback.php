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

namespace Unicity\VLD\Validation {

	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Locale;
	use \Unicity\ORM;
	use \Unicity\VLD;

	class Feedback extends Core\Object {

		/**
		 * @var Common\Mutable\HashSet
		 */
		protected $recommendations;

		/**
		 * @var string
		 */
		protected $root;

		/**
		 * @var Common\Mutable\HashSet
		 */
		protected $violations;

		public function __construct(string $root) {
			$this->root = $root;
			$this->recommendations = new Common\Mutable\HashSet();
			$this->violations = new Common\Mutable\HashSet();
		}

		public function addRecommendation(VLD\Validation\RuleType $type, array $fields, string $message, array $values = []) : void { # TODO make multilingual
			ksort($values);
			sort($fields);
			$this->recommendations->putValue([
				'fields' => static::mapFields($this->root, $fields),
				'message' => strtr(static::localize($message), $values),
				'type' => (string) $type,
			]);
		}

		public function addRecommendations(VLD\Validation\Feedback $feedback) : void {
			$this->recommendations->putValues($feedback->recommendations);
		}

		public function addViolation(VLD\Validation\RuleType $type, array $fields, string $message, array $values = []) : void { # TODO make multilingual
			ksort($values);
			sort($fields);
			$this->violations->putValue([
				'fields' => static::mapFields($this->root, $fields),
				'message' => strtr(static::localize($message), $values),
				'type' => (string) $type,
			]);
		}

		public function addViolations(VLD\Validation\Feedback $feedback) : void {
			$this->violations->putValues($feedback->violations);
		}

		public function getNumberOfRecommendations() : int {
			return $this->recommendations->count();
		}

		public function getNumberOfViolations() : int {
			return $this->violations->count();
		}

		public function toMap() : Common\IMap {
			$feedback = new ORM\JSON\Model\HashMap('\\Unicity\\VLD\\Validation\\Model\\Feedback');
			$feedback->recommendations = $this->recommendations;
			$feedback->violations = $this->violations;
			return $feedback;
		}

		protected static $localization = null;

		protected static function localize(string $message) {
			if (static::$localization === null) {
				static::$localization = static::localize_();
			}
			if (static::$localization->hasKey($message)) {
				return static::$localization->getValue($message);
			}
			return $message;
		}

		protected static function localize_() {
			$languages = Locale\Info::getLanguages();
			$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Messages';
			foreach ($languages as $language => $q) {
				$ext = '_' . str_replace('-', '_', $language) . '.properties';
				$uri = $file . $ext;
				if (file_exists($uri)) {
					return Config\Properties\Reader::load(new IO\File($uri))->read();
				}
			}
			$uri = $file . '.properties';
			return Config\Properties\Reader::load(new IO\File($uri))->read();
		}

		protected static function mapFields(string $root, array $fields) {
			$buffer = array();
			foreach ($fields as $i => $field) {
				$buffer[$i]['field'] = ORM\Query::appendKey($root, (string) $field);
			}
			return $buffer;
		}

	}

}