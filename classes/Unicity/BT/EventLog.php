<?php

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
				'status' => BT\Status::getName(BT\Status::ACTIVE),
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
			if ($exception instanceof \Throwable) {
				$context->exception = (object)[
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					'trace' => $exception->getTraceAsString(),
				];
			}

			$engine->getLogger('\\Unicity\\BT\\EventLog')->add(Log\Level::error(), "{$context->type}::process", Common\Collection::useArrays($context));
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

			$context->status = BT\Status::getName($joinPoint->getReturnedValue());

			$engine->getLogger('\\Unicity\\BT\\EventLog')->add(Log\Level::informational(), "{$context->type}::process", Common\Collection::useArrays($context));
		}

	}

}
