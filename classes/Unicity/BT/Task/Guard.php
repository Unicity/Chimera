<?php

declare(strict_types = 1);

namespace Unicity\BT\Task {

	use \Unicity\BT;

	/**
	 * This class represents a task guard.
	 *
	 * @access public
	 * @abstract
	 * @class
	 *
	 * @see https://sourcemaking.com/refactoring/replace-nested-conditional-with-guard-clauses
	 * @see http://www.tutisani.com/software-architecture/nested-if-vs-guard-condition.html
	 */
	abstract class Guard extends BT\Task\Leaf { }

}