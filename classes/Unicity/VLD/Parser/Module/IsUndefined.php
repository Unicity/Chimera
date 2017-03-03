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
	use \Unicity\Core;
	use \Unicity\VLD;
	use \Unicity\VLD\Parser\RuleType;

	class IsUndefined extends VLD\Parser\Module {

		public function process(BT\Entity $entity, array $paths): VLD\Parser\Feedback {
			$feedback = new VLD\Parser\Feedback();

			foreach ($paths as $path) {
				$v1 = $entity->getComponentAtPath($path);
				if (!Core\Data\ToolKit::isUndefined($v1)) {
					$feedback->addViolation(RuleType::mismatch(), [$path], 'value.compare.type.undefined');
				}
			}

			return $feedback;
		}

	}

}