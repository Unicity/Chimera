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

namespace Unicity\VLD\Parser\Module {

	use \Unicity\BT;
	use \Unicity\VLD;
	use \Unicity\VLD\Parser\RuleType;

	class IsIBAN extends VLD\Parser\Module {

		public function process(BT\Entity $entity, string $root, array $paths): VLD\Parser\Feedback {
			$feedback = new VLD\Parser\Feedback($root);

			foreach ($paths as $path) {
				if (!$this->isIBAN($entity->getComponentAtPath($path))) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.pattern');
				}
			}

			return $feedback;
		}

		public function isIBAN($iban) {
			if (is_string($iban)) {
				$iban = strtolower(str_replace(' ', '', $iban));
				$countries = array('al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16, 'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28, 'cz' => 24, 'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18, 'fr' => 27, 'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18, 'gt' => 28, 'hu' => 28, 'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27, 'jo' => 30, 'kz' => 20, 'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21, 'lt' => 20, 'lu' => 20, 'mk' => 19, 'mt' => 31, 'mr' => 27, 'mu' => 30, 'mc' => 27, 'md' => 24, 'me' => 22, 'nl' => 18, 'no' => 15, 'pk' => 24, 'ps' => 29, 'pl' => 28, 'pt' => 25, 'qa' => 29, 'ro' => 24, 'sm' => 27, 'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19, 'es' => 24, 'se' => 24, 'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23, 'gb' => 22, 'vg' => 24);
				$chars = array('a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23, 'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29, 'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35);
				if (strlen($iban) == $countries[substr($iban, 0, 2)]) {
					$MovedChar = substr($iban, 4) . substr($iban, 0, 4);
					$MovedCharArray = str_split($MovedChar);
					$NewString = "";
					foreach ($MovedCharArray AS $key => $value) {
						if (!is_numeric($MovedCharArray[$key])) {
							$MovedCharArray[$key] = $chars[$MovedCharArray[$key]];
						}
						$NewString .= $MovedCharArray[$key];
					}
					if (bcmod($NewString, '97') == 1) {
						return true;
					}
				}
			}
			return false;
		}

	}

}