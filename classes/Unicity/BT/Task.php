<?php

declare(strict_types = 1);

namespace Unicity\BT {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This class represents the base task for any task.
	 *
	 * @access public
	 * @abstract
	 * @class
	 * @see http://aigamedev.com/open/article/tasks/
	 * @see https://en.wikipedia.org/wiki/Behavior-driven_development
	 */
	abstract class Task extends Core\AbstractObject implements AOP\IAspect {

		/**
		 * This variable stores any data used for AOP.
		 *
		 * @access protected
		 * @var object
		 */
		protected $aop;

		/**
		 * This variable stores the policy associated with the task.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $policy;

		/**
		 * This variable stores the title of the task.
		 *
		 * @access protected
		 * @var string
		 */
		protected $title;

		/**
		 * This constructor initializes the class with the specified parameters.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the task's policy
		 */
		public function __construct(Common\Mutable\IMap $policy = null) {
			$this->aop = (object)[];
			$this->policy = ($policy !== null)
				? $policy
				: new Common\Mutable\HashMap();
			$this->title = '';
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->aop);
			unset($this->policy);
			unset($this->title);
		}

		/**
		 * This method runs before the concern's execution.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function before(AOP\JoinPoint $joinPoint) : void {
			$this->aop = BT\EventLog::before($joinPoint, $this->getTitle(), $this->getPolicy());
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) : void {
			BT\EventLog::afterReturning($joinPoint, $this->aop);
		}

		/**
		 * This method runs when the task's throws an exception.
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterThrowing(AOP\JoinPoint $joinPoint) : void {
			BT\EventLog::afterThrowing($joinPoint, $this->aop);
		}

		/**
		 * This method returns the policy associated with this task.
		 *
		 * @access public
		 * @return Common\Mutable\IMap                              the policy associated with the
		 *                                                          task
		 */
		public function getPolicy() : Common\Mutable\IMap {
			return $this->policy;
		}

		/**
		 * This method returns the title associated with this task.
		 *
		 * @access public
		 * @return string                                           the title associated with the
		 *                                                          task
		 */
		public function getTitle() : ?string {
			return $this->title;
		}

		/**
		 * This method return the weight given to this task.
		 *
		 * @access public
		 * @return double                                           the weight given to this task
		 */
		public function getWeight() : float {
			return 0.0;
		}

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @abstract
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public abstract function process(BT\Engine $engine, string $entityId) : int;

		/**
		 * This method resets the task.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine
		 */
		public function reset(BT\Engine $engine) : void {
			// do nothing
		}

		/**
		 * This method sets the task's policy.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the policy to be associated
		 *                                                          with this task
		 */
		public function setPolicy(Common\Mutable\IMap $policy) : void {
			$this->policy = $policy;
		}

		/**
		 * This method sets the title to be associated with this task.
		 *
		 * @access public
		 * @param string $title                                     the title to be associated
		 *                                                          with this task
		 */
		public function setTitle(?string $title) {
			$this->title = $title;
		}

		/**
		 * This method returns a string representing this task.
		 *
		 * @access public
		 * @return string                                           a string representing this task
		 */
		public function __toString() {
			return $this->getTitle();
		}

	}

}
