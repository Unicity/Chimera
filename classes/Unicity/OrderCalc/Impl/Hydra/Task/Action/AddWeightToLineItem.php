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

namespace Unicity\OrderCalc\Impl\Hydra\Task\Action {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\FP;
	use \Unicity\IO;
	use \Unicity\Log;

	class AddWeightToLineItem extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			foreach ($order->lines->items as $index => $line) {
				$this->aop['lines']['items'][$index]['item']['weightEach']['value'] = $line->item->weightEach->value;
				$this->aop['lines']['items'][$index]['item']['weightEach']['unit'] = $line->item->weightEach->unit;
			}
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) : int {
			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$items = $this->getItems(); // ['item,weight']
			$items = FP\IList::foldLeft($items, function(Common\Mutable\HashMap $c, string $x) {
				$v = array_map('trim', explode(',', $x));
				$c->putEntry($v[0], floatval($v[1]));
				return $c;
			}, new Common\Mutable\HashMap());
			$unit = $this->policy->getValue('unit'); // 'kg' or 'lbs'

			foreach ($order->lines->items as $line) {
				$item = trim(Core\Convert::toString($line->item->id->unicity));
				if ($items->hasKey($item)) {
					$line->item->weightEach->value = $items->getValue($item);
					$line->item->weightEach->unit = $unit;
				}
			}

			return BT\Status::SUCCESS;
		}

		/**
		 * This method returns a list of items with their respective weight.
		 *
		 * @access private
		 * @return Common\Mutable\ArrayList                         the items and their respective
		 *                                                          weights
		 */
		private function getItems() {
			if ($this->policy->hasKey('data-source')) {
				$data_source = $this->policy->getValue('data-source');
				$items = new Common\Mutable\ArrayList();
				$file = new IO\File($data_source);
				if ($file->exists()) {
					if ($handle = @fopen((string) $file, 'r')) {
						while (($line = fgets($handle)) !== false) {
							$line = trim($line);
							if (($line != '') && ($line[0] != '#')) {
								$items->addValue($line);
							}
						}
						fclose($handle);
					}
				}
				return $items;
			}
			return  $this->policy->getValue('items');
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) : void {
			$engine = $joinPoint->getArgument(0);
			$entityId = $joinPoint->getArgument(1);

			$entity = $engine->getEntity($entityId);
			$order = $entity->getComponent('Order');

			$message = array(
				'changes' => array(),
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'tags' => array(),
				'title' => $this->getTitle(),
			);

			foreach ($order->lines->items as $index => $line) {
				$message['changes'][] = array(
					'field' => "Order.lines.items[{$index}].item.weightEach.value",
					'from' => $this->aop['lines']['items'][$index]['item']['weightEach']['value'],
					'to' => $line->item->weightEach->value,
				);
				$message['changes'][] = array(
					'field' => "Order.lines.items[{$index}].item.weightEach.unit",
					'from' => $this->aop['lines']['items'][$index]['item']['weightEach']['unit'],
					'to' => $line->item->weightEach->unit,
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