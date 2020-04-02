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
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Common;
	use \Unicity\Log;

	class EventLog {

		public static function before(AOP\JoinPoint $joinPoint, string $title, Common\HashMap $policy, array $inputs = [], array $variants = []) : object {
			$entity = $joinPoint->getArgument(0)->getEntity($joinPoint->getArgument(1));
			return (object) [
				'type' => $joinPoint->getProperty('class'),
				'title' => $title,
				'policy' => $policy,
				'inputs' => array_map(function($path) use ($entity) {
					return (object)[
						'path' => $path,
						'value' => Core\Data\ToolKit::ifUndefined($entity->getComponentAtPath($path), null),
					];
				}, $inputs),
				'changes' => array_map(function($path) use ($entity) {
					return (object)[
						'path' => $path,
						'before' => Core\Data\ToolKit::ifUndefined($entity->getComponentAtPath($path), null),
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
		 * @param \stdClass $context                                the context to be enriched and logged
		 */
		public function afterThrowing(AOP\JoinPoint $joinPoint, \stdClass $context) : void {
			$context = json_decode(Config\JSON\Helper::encode($context));

			$engine = $joinPoint->getArgument(0);

			$context->changes = [];

			$exception = $joinPoint->getException();
			if ($exception instanceof \Exception) {
				$context->exception = (object)[
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					'trace' => $exception->getTraceAsString(),
				];
			}

			$context->status = $joinPoint->getReturnedValue();

			$engine->getLogger()->add(Log\Level::error(), "{$context->type}::process", Common\Collection::useArrays($context));
			$joinPoint->setReturnedValue(BT\Status::ERROR);
			$joinPoint->setException(null);
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 * @param \stdClass $context                                the context to be enriched and logged
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint, \stdClass $context) : void {
			$context = json_decode(Config\JSON\Helper::encode($context));

			$engine = $joinPoint->getArgument(0);

			$entity = $engine->getEntity($joinPoint->getArgument(1));

			$context->changes = array_map(function($change) use ($entity) {
				$change->after = Core\Data\ToolKit::ifUndefined($entity->getComponentAtPath($change->path), null);
				return $change;
			}, $context->changes);

			$context->status = $joinPoint->getReturnedValue();

			$engine->getLogger()->add(Log\Level::informational(), "{$context->type}::process", Common\Collection::useArrays($context));
		}

	}

}