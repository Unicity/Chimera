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
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Log;

	class HasItemDescription extends BT\Task\Guard {

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

			$pattern = $this->policy->getValue('pattern');

			foreach ($order->lines->items as $line) {
				$description = trim(Core\Convert::toString($line->catalogSlide->content->description));
				if (preg_match($pattern, $description) && ($line->quantity > 0)) {
					return BT\Status::SUCCESS;
				}
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
				'input' => array(),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'tags' => array(),
				'title' => $this->getTitle(),
			);

			foreach ($order->lines->items as $index => $line) {
				$message['input'][] = array(
					'field' => "Order.lines.items[{$index}].catalogSlide.content.description",
					'value' => $line->catalogSlide->content->description,
				);
				$message['input'][] = array(
					'field' => "Order.lines.items[{$index}].quantity",
					'value' => $line->quantity,
				);
			}

			$blackboard = $engine->getBlackboard('global');
			if ($blackboard->hasKey('tags')) {
				$tags = $blackboard->getValue('tags');
				foreach ($tags as $path) {
					if ($entity->hasComponentAtPath($path)) {
						$message['tags'][] = array(
							'name' => $path,
							'value' => $entity->getComponentAtPath($path),
						);
					}
				}
			}

			$engine->getLogger()->add(Log\Level::informational(), json_encode(Common\Collection::useArrays($message)));
		}

	}

}
