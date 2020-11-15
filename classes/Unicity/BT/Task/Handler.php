<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Core;

	/**
	 * This class represents a process handler.
	 *
	 * @access public
	 * @class
	 */
	class Handler extends Core\AbstractObject {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @static
		 * @param BT\Task $task                                     the task to do the processing
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public static function process(BT\Task $task, BT\Engine $engine, string $entityId) {
			$args = func_get_args();
			array_shift($args);

			return AOP\Advice::factory(new AOP\JoinPoint($args, ['class' => $task->__getClass(), 'method' => 'process']))
				->register($task)
				->execute([$task, 'process']);
		}

	}

}