<?php

/**
 * Copyright 2015-2020 Unicity International
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
	use \Unicity\Core;

	class SaveTermsOverride extends BT\Task\Action {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [
				'Order.terms.discount.amount',
				'Order.terms.freight.amount',
				'Order.terms.pv',
				'Order.terms.subtotal',
				'Order.terms.taxableTotal',
				'Order.terms.timbre.amount',
				'Order.terms.total',
			], $variants = [
				'TermsOverride',
			]);
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

			$termsJSON = \Unicity\Config\JSON\Helper::marshal($order->terms);

			$reader = new \Unicity\Config\JSON\Reader($termsJSON, [
				'assoc' => true,
			]);

			$terms = \Unicity\ORM\JSON\Model\Marshaller::unmarshal($reader, [
				'case_sensitive' => true,
				'schema' => '\Unicity\MappingService\Impl\Hydra\API\Master\Model\Terms',
			]);

			$entity->setComponent('TermsOverride', $terms);

			return BT\Status::SUCCESS;
		}

	}

}