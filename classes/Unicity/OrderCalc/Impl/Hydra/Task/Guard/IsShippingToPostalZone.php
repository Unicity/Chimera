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

namespace Unicity\OrderCalc\Impl\Hydra\Task\Guard {

	use \Leap\Core\DB;
	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Log;

	class IsShippingToPostalZone extends BT\Task\Guard {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) {
			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$data_source = $this->policy->getValue('data-source');
			$table = $this->policy->getValue('table');

			$records = DB\SQL::select($data_source)
				->from($table)
				->where('PostalCode', '=', $order->shipToAddress->zip)
				->where('Zone', '=', $this->policy->getValue('zone'))
				->limit(1)
				->query();

			if ($records->is_loaded()) {
				return BT\Status::SUCCESS;
			}

			return BT\Status::FAILED;
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$message = array(
				'class' => $joinPoint->getProperty('class'),
				'input' => array(
					array(
						'field' => 'Order.shipToAddress.zip',
						'value' => $order->shipToAddress->zip,
					),
				),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'tags' => array(),
				'task' => 'guard',
				'title' => $this->getTitle(),
			);

			$blackboard = $engine->getBlackboard('global');
			if ($blackboard->hasKey('tags')) {
				$tags = $blackboard->getValue('tags');
				foreach ($tags as $path) {
					$message['tags'][] = array(
						'name' => $path,
						'value' => $entity->getComponentAtPath($path),
					);
				}
			}

			Log\Logger::log(Log\Level::informational(), json_encode(Common\Collection::useArrays($message)));
		}

	}

}