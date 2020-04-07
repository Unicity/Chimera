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

declare(strict_types=1);

namespace Unicity\OrderCalc\Impl\Hydra\Task\Action {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Common;
	use \Unicity\FP;
	use \Unicity\Hateoas;
	use \Unicity\ORM;
	use \Unicity\Trade;

	class ApplyPromotions extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine the engine running
		 * @param string $entityId the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId): int {
			$entity = $engine->getEntity($entityId);

			$order = $entity->getComponent('Order');
			$promotions = $entity->getComponent('Promotions');
			$event = $entity->getComponent('Event');

			$promotions->items = FP\IList::filter($promotions->items, function($promotion) use ($order, $event) {
				if ($this->matchMap($promotion->stats->eventDetails, $promotion->pattern->eventDetails, $order, $event, '')) {
					$this->apply($promotion->patch->eventDetails, $order);
					return true;
				}
				return false;
			});

			return BT\Status::SUCCESS;
		}

		public function apply($patch, $order): void {
			if (ORM\Query::hasPath($patch, 'added_lines.items')) {
				foreach ($patch->added_lines->items as $item) {
					$order->added_lines->items->addValue($item);
				}
			}

			if (ORM\Query::hasPath($patch, 'terms.freight.amount')) {
				$order->terms->freight->amount = Trade\Money::make($patch->terms->freight->amount, $order->currency)
					->getConvertedAmount();
			}

			if (ORM\Query::hasPath($patch, 'terms.discount.percentage')) {
				if ($patch->terms->discount->percentage > $order->terms->discount->percentage) {
					$order->terms->discount->amount = Trade\Money::make($order->terms->subtotal, $order->currency)
						->multiply($patch->terms->discount->percentage / 100)
						->getConvertedAmount();
					$order->terms->discount->percentage = $patch->terms->discount->percentage / 100;
				}
			}
			else if (ORM\Query::hasPath($patch, 'terms.discount.amount')) {
				$order->terms->discount->amount = Trade\Money::make($order->terms->discount->amount, $order->currency)
					->add(Trade\Money::make($patch->terms->discount->amount, $order->currency))
					->getConvertedAmount();
				if ($order->terms->discount->amount > $order->terms->subtotal) {
					$order->terms->discount->amount = $order->terms->subtotal;
				}
			}

			$paths = [ // paths for only customer ids
				'customer.enroller.id.unicity',
				'customer.sponsor.id.unicity',
			];

			foreach ($paths as $path) {
				if (ORM\Query::hasPath($patch, $path)) {
					$value = ORM\Query::getValue($patch, $path);
					ORM\Query::setValue($order, preg_replace('/id\.unicity$/', 'href', $path), Hateoas\Link::href('customers', Hateoas\Link::encrypt("?unicity={$value}")));
					ORM\Query::setValue($order, $path, $value);
				}
			}

			$paths = [
				'customer.type',
			];

			foreach ($paths as $path) {
				if (ORM\Query::hasPath($patch, $path)) {
					ORM\Query::setValue($order, $path, ORM\Query::getValue($patch, $path));
				}
			}
		}

		protected function isAny($expr, $value) {
			return (empty($expr) && empty($value)) || (!empty($expr) && preg_match('/^(' . implode('|', array_map('preg_quote', explode('|', $expr))) . ')$/i', $value));
		}

		protected function isLikeAny($expr, $value) {
			return (empty($expr) && empty($value)) || (!empty($expr) && preg_match('/(' . implode('|', array_map('preg_quote', explode('|', $expr))) . ')/i', $value));
		}

		protected function matchArray($stats, $pattern, $order, $event, $path): bool {
			return $this->reduce($pattern, function(bool $carry, array $tuple) use ($stats, $order, $event, $path) {
				$v1 = $tuple[0];
				$a2 = ORM\Query::getValue($order, $path);
				return $this->reduce($a2, function(bool $carry, array $tuple) use ($stats, $order, $event, $path, $v1) {
					$ipath = ORM\Query::appendIndex($path, $tuple[1]);
					$v2 = ORM\Query::getValue($order, $ipath);

					$d1 = Core\DataType::info($v1);
					$d2 = Core\DataType::info($v2);

					if (($d1->class === $d2->class) && ($d1->type === $d2->type)) {
						if ($v1 instanceof Common\IList) {
							return $carry || $this->matchArray($stats, $v1, $order, $event, $ipath);
						}
						if ($v1 instanceof Common\IMap) {
							return $carry || $this->matchMap($stats, $v1, $order, $event, $ipath);
						}
						if (in_array($d1->type, ['integer', 'double'])) {
							return $carry || ($v2 >= $v1);
						}
						if (in_array($d1->type, ['string'])) {
							return $carry || $this->isAny($v1, $v2);
						}
						if ($d1->hash === $d2->hash) {
							return true;
						}
					}
					return $carry;
				}, false);
			}, true);
		}

		public function matchMap($stats, $pattern, $order, $event, $path): bool {
			return $this->reduce($pattern, function(bool $carry, array $tuple) use ($stats, $order, $event, $path) {
				$k1 = $tuple[1];
				$v1 = $tuple[0];
				$kpath = ORM\Query::appendKey($path, $k1);
				if ($kpath === 'dateStarts') {
					$tpath = ORM\Query::appendKey($path, 'dateCreated');
					if (ORM\Query::hasPath($order, $tpath)) {
						$v2 = date('Y-m-d', strtotime(ORM\Query::getValue($order, $tpath)));
						if (strcmp($v2, $v1) >= 0) {
							return $carry;
						}
					}
					return false;
				}
				if ($kpath === 'dateEnds') {
					$tpath = ORM\Query::appendKey($path, 'dateCreated');
					if (ORM\Query::hasPath($order, $tpath)) {
						$v2 = date('Y-m-d', strtotime(ORM\Query::getValue($order, $tpath)));
						if (strcmp($v2, $v1) <= 0) {
							return $carry;
						}
					}
					return false;
				}
				if ($kpath === 'limit') {
					return $carry && ($stats->counter < $v1);
				}
				if ($kpath === 'customer.limit') {
					return $carry && ($stats->customer->counter < $v1);
				}
				if ($kpath === 'employee') {
					return $carry && (isset($event->eventSecurityContext->employee) && isset($event->eventSecurityContext->employee->username));
				}
				if (ORM\Query::hasPath($order, $kpath)) {
					$v2 = ORM\Query::getValue($order, $kpath);

					$d1 = Core\DataType::info($v1);
					$d2 = Core\DataType::info($v2);

					if (($d1->class === $d2->class) && ($d1->type === $d2->type)) {
						if ($v1 instanceof Common\IList) {
							return $carry && $this->matchArray($stats, $v1, $order, $event, $kpath);
						}
						if ($v1 instanceof Common\IMap) {
							return $carry && $this->matchMap($stats, $v1, $order, $event, $kpath);
						}
						if (in_array($d1->type, ['integer', 'double'])) {
							return $carry && ($v2 >= $v1);
						}
						if (in_array($d1->type, ['string'])) {
							if (preg_match('/^lines\.items\.(0|[1-9][0-9]*)\.(kitChildren\.(0|[1-9][0-9]*)\.)?catalogSlide\.content\.description$/', $kpath)) {
								return $carry && $this->isLikeAny($v1, $v2);
							}
							if (preg_match('/^\!\((.*)\)$/', $v1, $matches)) {
								return $carry && !$this->isAny($matches[1], $v2);
							}
							return $carry && $this->isAny($v1, $v2);
						}
						if ($d1->hash === $d2->hash) {
							return $carry;
						}
					}
				}
				return false;
			}, true);
		}

		protected function reduce($collection, $callback, $initial) {
			$c = $initial;
			foreach ($collection as $k => $v) {
				$c = $callback($c, array($v, $k));
			}
			return $c;
		}

	}

}
