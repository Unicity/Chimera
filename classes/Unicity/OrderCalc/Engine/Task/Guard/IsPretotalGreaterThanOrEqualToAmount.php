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

namespace Unicity\OrderCalc\Engine\Task\Guard {

	use \Unicity\BT;
	use \Unicity\Core;

	class IsPretotalGreaterThanOrEqualToAmount extends BT\Task\Guard {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param string $entityId                                  the entity id being processed
		 * @param BT\Engine $engine                                 the engine
		 * @return integer                                          the status
		 */
		public function process(string $entityId, BT\Engine $engine) {
			$order = $engine->getEntity($entityId)->getComponent('Order');

			$amount = Core\Convert::toDouble($this->policy->getValue('amount'));

			if ($order->terms->pretotal >= $amount) {
				return BT\Status::SUCCESS;
			}

			return BT\Status::FAILED;
		}

	}

}
