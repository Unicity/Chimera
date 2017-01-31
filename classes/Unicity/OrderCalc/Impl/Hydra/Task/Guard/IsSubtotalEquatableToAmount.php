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

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Log;

	class IsSubtotalEquatableToAmount extends BT\Task\Guard {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) {
			$order = $engine->getEntity($entityId)->getComponent('Order');

			$operator = Core\Convert::toString($this->policy->getValue('operator'));
			$amount = Core\Convert::toDouble($this->policy->getValue('amount'));

			if (Core\Operator::isEquatable($order->terms->subtotal, $operator, $amount)) {
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

			$order = $engine->getEntity($entityId)->getComponent('Order');

			$message = array(
				'class' => $joinPoint->getProperty('class'),
				'inputs' => array(
					array(
						'field' => 'terms.subtotal',
						'value' => $order->terms->subtotal,
					),
				),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'task' => 'guard',
				'title' => $this->getTitle(),
			);

			Log\Logger::log(Log\Level::informational(), json_encode($message));
		}

	}

}
