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

namespace Unicity\VLD\Parser\Definition {

	use \Unicity\VLD;

	class DumpStatement extends VLD\Parser\Definition\Statement {

		public function get() {
			$paths = (isset($this->args['paths'])) ? $this->args['paths']->get() : array();

			$entity = $this->context->getEntity();
			if (count($paths) > 0) {
				$components = array_map(function($path) use($entity) {
					return $entity->getComponentAtPath($path);
				}, $paths);
			}
			else {
				$components = array($this->context->getEntity()->getComponents());
			}
			call_user_func_array('var_dump', $components);

			return new VLD\Parser\Feedback($this->context->getPath());
		}

	}

}