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

namespace Unicity\BT {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Log;

	class EventLog {

		public static function before(AOP\JoinPoint $joinPoint, string $title, Common\HashMap $policy, array $inputs = [], array $variants = []) : object {
			$entity = $joinPoint->getArgument(0)->getEntity($joinPoint->getArgument(1));
			return (object)[
				'type' => $joinPoint->getProperty('class'),
				'title' => $title,
				'policy' => $policy,
				'inputs' => array_map(function($path) use ($entity) {
					return (object)[
						'path' => $path,
						'value' => $entity->getComponentAtPath($path),
					];
				}, $inputs),
				'changes' => array_map(function($path) use ($entity) {
					return (object)[
						'path' => $path,
						'before' => $entity->getComponentAtPath($path),
					];
				}, $variants),
				'status' => BT\Status::ACTIVE,
				'exception' => null,
			];
		}

		/**
		 * This method runs when the task's throws an exception.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 * @param \stdClass $message                                the message to be enriched and logged
		 */
		public function afterThrowing(AOP\JoinPoint $joinPoint, \stdClass $message) : void {
			$message = json_decode(json_encode($message));

			$engine = $joinPoint->getArgument(0);

			$message->changes = [];

			$exception = $joinPoint->getException();
			if ($exception instanceof \Exception) {
				$message->exception = (object)[
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					'trace' => $exception->getTraceAsString(),
				];
			}

			$message->status = $joinPoint->getReturnedValue();

			$engine->getLogger()->add(Log\Level::error(), json_encode(Common\Collection::useArrays($message)));
			$joinPoint->setReturnedValue(BT\Status::ERROR);
			$joinPoint->setException(null);
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 * @param \stdClass $message                                the message to be enriched and logged
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint, \stdClass $message) : void {
			$message = json_decode(json_encode($message));

			$engine = $joinPoint->getArgument(0);

			$entity = $engine->getEntity($joinPoint->getArgument(1));

			$message->changes = array_map(function($change) use ($entity) {
				$change->after = $entity->getComponentAtPath($change->path);
				return $change;
			}, $message->changes);

			$message->status = $joinPoint->getReturnedValue();

			$engine->getLogger()->add(Log\Level::informational(), json_encode(Common\Collection::useArrays($message)));
		}

	}

}