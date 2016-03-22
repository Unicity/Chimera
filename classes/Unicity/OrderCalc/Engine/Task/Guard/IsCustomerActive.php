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

namespace Unicity\OrderCalc\Engine\Task\Guard {

	use \Unicity\BT;

	class IsCustomerActive extends BT\Task\Guard {

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @return BT\State                                         the state
		 */
		public function process(BT\Entity $entity) {
			$order = $entity->getBody()->Order;

			$status = $order->customer->status;
			if (in_array($status, array('Suspended', 'Terminated'))) { // TODO abstract out to a config file
				return BT\State\Error::with($entity);
			}

			$type = $order->customer->type;
			if (in_array($type, array('LegacySuspended', 'LegacyTerminated'))) { // TODO abstract out to a config file
				return BT\State\Error::with($entity);
			}

			return BT\State\Success::with($entity);
		}

	}

}
