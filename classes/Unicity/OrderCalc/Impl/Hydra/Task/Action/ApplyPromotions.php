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

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\Common;
	use \Unicity\FP;
	use \Unicity\ORM;
	use \Unicity\Trade;

	class ApplyPromotions extends BT\Task\Action {

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
			$promotions = $entity->getComponent('Promotions');

			$promotions->items = FP\IList::filter($promotions->items, function($promotion) use($order) {
				if ($this->matchMap($order, $promotion->pattern->eventDetails, '', $promotion->counter, $promotion->customer->counter)) {
					$this->patch($order, $promotion->patch);
					return true;
				}
				return false;
			});

			return BT\Status::SUCCESS;
		}

		protected function matchArray($order, $pattern, $path, $ord_ctr, $cust_ctr) : bool {
			return $this->reduce($pattern, function (bool $carry, array $tuple) use ($order, $path, $ord_ctr, $cust_ctr) {
				$v1 = $tuple[0];
				$a2 = ORM\Query::getValue($order, $path);
				foreach ($a2 as $i2 => $v2) {
					$ipath = ORM\Query::appendIndex($path, $i2);
					$v2 = ORM\Query::getValue($order, $ipath);

					$d1 = Core\DataType::info($v1);
					$d2 = Core\DataType::info($v2);

					if (($d1->class === $d2->class) && ($d1->type === $d2->type)) {
						if ($v1 instanceof Common\IList) {
							return $carry && $this->matchArray($order, $v1, $ipath, $ord_ctr, $cust_ctr);
						}
						if ($v1 instanceof Common\IMap) {
							return $carry && $this->matchMap($order, $v1, $ipath, $ord_ctr, $cust_ctr);
						}
						if ($d1->hash === $d2->hash) {
							return $carry;
						}
					}
				}
				return false;
			}, true);
		}

		public function matchMap($order, $pattern, $path, $ord_ctr, $cust_ctr) : bool {
			return $this->reduce($pattern, function (bool $carry, array $tuple) use ($order, $path, $ord_ctr, $cust_ctr) {
				$k1 = $tuple[1];
				$v1 = $tuple[0];

				$kpath = ORM\Query::appendKey($path, $k1);
				if (ORM\Query::hasPath($order, $kpath)) {
					$v2 = ORM\Query::getValue($order, $kpath);

					$d1 = Core\DataType::info($v1);
					$d2 = Core\DataType::info($v2);

					if (($d1->class === $d2->class) && ($d1->type === $d2->type)) {
						if ($v1 instanceof Common\IList) {
							return $carry && $this->matchArray($order, $v1, $kpath, $ord_ctr, $cust_ctr);
						}
						if ($v1 instanceof Common\IMap) {
							return $carry && $this->matchMap($order, $v1, $kpath, $ord_ctr, $cust_ctr);
						}
						if ($d1->hash === $d2->hash) {
							return $carry;
						}
					}
				}
				else {
					if ($kpath === 'dateStarts') {
						$tpath = ORM\Query::appendKey($path, 'dateCreated');
						if (ORM\Query::hasPath($order, $tpath)) {
							$v2 = date('Y-m-d', strtotime(ORM\Query::getValue($order, $tpath)));
							if (strcmp($v2, $v1) >= 0) {
								return $carry;
							}
						}
					}
					if ($kpath === 'dateEnds') {
						$tpath = ORM\Query::appendKey($path, 'dateCreated');
						if (ORM\Query::hasPath($order, $tpath)) {
							$v2 = date('Y-m-d', strtotime(ORM\Query::getValue($order, $tpath)));
							if (strcmp($v2, $v1) <= 0) {
								return $carry;
							}
						}
					}

					if (($kpath === 'limit') && ($ord_ctr >= $v1)) {
						return $carry;
					}

					if (($kpath === 'customer.limit') && ($cust_ctr >= $v1)) {
						return $carry;
					}
				}
				return false;
			}, true);
		}

		public function patch($order, $patch) : void {
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
			else if (ORM\Query::hasPath($patch, 'terms.discount.amount')) { // TODO handle discounts greater than subtotal
				if ($patch->terms->discount->amount > $order->terms->discount->amount) {
					$order->terms->discount->amount = Trade\Money::make($patch->terms->discount->amount, $order->currency)
						->getConvertedAmount();
				}
			}

			$paths = [
				'customer.enroller.id.unicity',
				'customer.sponsor.id.unicity',
				'customer.type',
			];

			foreach ($paths as $path) {
				if (ORM\Query::hasPath($patch, $path)) {
					ORM\Query::setValue($order, $path, ORM\Query::getValue($patch, $path));
				}
			}
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