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

namespace Unicity\OrderCalc\Impl\Hydra\Task\Guard {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Core;

	class IsDateTimeWithinRange extends BT\Task\Guard {

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy(), $inputs = [
				'Event.eventContext.Date',
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
			$event = $entity->getComponent('Event');

			$timezone = static::getPolicyTimeZone($this->policy);
			$current = new \DateTime($event->eventContext->Date, $timezone);

			$start = static::getPolicyDateTime($this->policy, 'start', $timezone);
			if (!empty($start) && ($current < $start)) { // inclusive
				return BT\Status::FAILED;
			}

			$end = static::getPolicyDateTime($this->policy, 'end', $timezone);
			if (!empty($end) && ($current >= $end)) { // exclusive
				return BT\Status::FAILED;
			}

			return BT\Status::SUCCESS;
		}

		private static function getPolicyDateTime($policy, $field, $timezone) : ?\DateTime {
			if ($policy->hasKey("{$field}.date")) {
				$date = Core\Convert::toString($policy->getValue("{$field}.date"));
				if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
					return new \DateTime(
						implode('T', [$date, static::getPolicyTime($policy, $field)]),
						$timezone
					);
				}
			}
			return null;
		}

		private static function getPolicyTime($policy, $field) : string {
			if ($policy->hasKey("{$field}.time")) {
				$time = Core\Convert::toString($policy->getValue("{$field}.time"));
				if (preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $time)) {
					return $time;
				}
			}
			return '00:00:00';
		}

		public static function getPolicyTimeZone($policy) : \DateTimeZone {
			return new \DateTimeZone($policy->hasKey('timezone')
				? Core\Convert::toString($policy->getValue('timezone'))
				: 'America/Denver'
			);
		}

	}

}
