<?php

/**
 * Copyright 2015 Unicity International
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

namespace Unicity\BT {

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
	abstract class Task extends Core\Object {

		/**
		 * This constant represents the default namespace used by Spring XML for behavior
		 * trees.
		 *
		 * @const string
		 */
		const NAMESPACE_URI = 'http://static.unicity.com/modules/xsd/spring-bt.xsd';

		/**
		 * This variable stores a reference to the blackboard.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $blackboard;

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
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be used
		 * @param Common\Mutable\IMap $policy                       the policy associated with the task
		 */
		public function __construct(Common\Mutable\IMap $blackboard = null, Common\Mutable\IMap $policy = null) {
			$this->blackboard = ($blackboard !== null)
				? $blackboard
				: new Common\Mutable\HashMap();
			$this->title = '';
			$this->policy = ($policy !== null)
				? $policy
				: new Common\Mutable\HashMap();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->blackboard);
			unset($this->policy);
			unset($this->title);
		}

		/**
		 * This method is called after each call to the process method.
		 *
		 * @access public
		 */
		public function after() {
			// do nothing
		}

		/**
		 * This method is called before each call to the process method.
		 *
		 * @access public
		 */
		public function before() {
			// do nothing
		}

		/**
		 * This method returns a reference to the blackboard used by the task.
		 *
		 * @access public
		 * @return Common\Mutable\IMap                              the blackboard used by the task
		 */
		public function getBlackboard() {
			return $this->blackboard;
		}

		/**
		 * This method returns the policy associated with this task.
		 *
		 * @access public
		 * @return Common\Mutable\IMap                              the policy associated with the
		 *                                                          task
		 */
		public function getPolicy() {
			return $this->policy;
		}

		/**
		 * This method returns the title associated with this task.
		 *
		 * @access public
		 * @return string                                           the title associated with the
		 *                                                          task
		 */
		public function getTitle() {
			return $this->title;
		}

		/**
		 * This method return the weight given to this task.
		 *
		 * @acces public
		 * @return double                                           the weight given to this task
		 */
		public function getWeight() {
			return 0.0;
		}

		/**
		 * This method processes the models and returns the status.
		 *
		 * @access public
		 * @abstract
		 * @param BT\Exchange $exchange                             the exchange given to process
		 * @return integer                                          the status code
		 */
		public abstract function process(BT\Exchange $exchange);

		/**
		 * This method resets the task.
		 *
		 * @access public
		 */
		public function reset() {
			// do nothing
		}

		/**
		 * This method sets the task's blackboard.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $blackboard                   the blackboard to be set
		 */
		public function setBlackboard(Common\Mutable\IMap $blackboard) {
			$this->blackboard = $blackboard;
		}

		/**
		 * This method sets the task's policy.
		 *
		 * @access public
		 * @param Common\Mutable\IMap $policy                       the policy to be associated
		 *                                                          with this task
		 */
		public function setPolicy(Common\Mutable\IMap $policy  ) {
			$this->policy = $policy;
		}

		/**
		 * This method sets the title to be associated with this task.
		 *
		 * @access public
		 * @param string $title                                     the title to be associated
		 *                                                          with this task
		 */
		public function setTitle($title) {
			$this->title = Core\Convert::toString($title);
		}

		/**
		 * This method returns a string representing this task.
		 *
		 * @acces public
		 * @return string                                           a string representing this task
		 */
		public function __toString() {
			return $this->getTitle();
		}

	}

}