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

namespace Unicity\Automaton {

	use \Unicity\Automaton;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Throwable;

	/**
	 * This class represents a transition in an automaton.
	 *
	 * @access public
	 * @class
	 * @package Automaton
	 */
	class Transition extends Core\Object implements Automaton\ITransition {

		/**
		 * This variable stores the actions that will be executed when the transition is
		 * traversed.
		 *
		 * @access protected
		 * @var \Unicity\Common\IList
		 */
		protected $actions;

		/**
		 * This variable stores the cost (sometimes referred to as the weight) assigned to
		 * the transition.
		 *
		 * @access protected
		 * @var double
		 */
		protected $cost;

		/**
		 * This variable stores the id associated with the transition.
		 *
		 * @access protected
		 * @var string
		 */
		protected $id;

		/**
		 * This variable stores the input symbol associated with the transition.
		 *
		 * @access protected
		 * @var mixed
		 */
		protected $condition;

		/**
		 * This variable stores the target states for the transition.
		 *
		 * @access protected
		 * @var \Unicity\Common\Mutable\ISet
		 */
		protected $targets;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param string $id                                        the id given to the transition
		 * @param mixed $targets                                    a set of target states
		 * @param mixed $condition                                  the condition to take transition
		 * @param mixed $actions                                    the actions triggered "on transition"
		 * @param double $cost                                      the cost associated with the transition
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates that the specified argument
		 *                                                          is not a callable
		 */
		public function __construct($id, $targets = null, $condition = null, $actions = null, $cost = 0.0) {
			$this->id = $id;

			$this->targets = new Common\Mutable\HashSet();
			if ($targets !== null) {
				if (is_array($targets) || ($targets instanceof \Traversable)) {
					$this->addTargets($targets);
				}
				else {
					$this->addTarget($targets);
				}
			}

			$this->condition = $condition;

			$this->actions = new Common\Mutable\ArrayList();
			if ($actions !== null) {
				if (is_array($actions) || ($actions instanceof \Traversable)) {
					$this->addActions($actions);
				}
				else {
					$this->addAction($actions);
				}
			}

			$this->cost = $cost;
		}

		/**
		 * This method adds an action to the transition.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IAction $action                an action to be added to the
		 *                                                          transition
		 */
		public function addAction(Automaton\IAction $action) {
			$this->actions->addValue($action);
		}

		/**
		 * This method adds a list of actions to the transition.
		 *
		 * @access public
		 * @param mixed $actions                                    a list of actions to be added to
		 *                                                          the transition
		 */
		public function addActions($actions) {
			if ($actions !== null) {
				foreach ($actions as $action) {
					$this->addAction($action);
				}
			}
		}

		/**
		 * This method adds a target state.
		 *
		 * @access public
		 * @param mixed $target                                     a target state to be added
		 */
		public function addTarget($target) {
			if ($target !== null) {
				$tid = ($target instanceof Automaton\IState) ? $target->getId() : '' . $target;
				$this->targets->putValue($tid);
			}
		}

		/**
		 * This method adds a set of target states.
		 *
		 * @access public
		 * @param \Traversable $targets                             a set of target states to be
		 *                                                          added
		 */
		public function addTargets($targets) {
			if ($targets !== null) {
				foreach ($targets as $target) {
					$this->addTarget($target);
				}
			}
		}

		/**
		 * This method compares the specified object with the current object for order.
		 *
		 * @access public
		 * @param \Unicity\Automaton\ITransition $object            the object to be compared
		 * @return integer                                          a negative integer, zero, or a positive
		 *                                                          integer as this object is less than,
		 *                                                          equal to, or greater than the specified
		 *                                                          object
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates that the object must be of the
		 *                                                          same type
		 */
		public function compareTo($object) {
			if (!($object instanceof Automaton\ITransition)) {
				throw new Throwable\InvalidArgument\Exception('Invalid comparison. Object must be an instance of ITransition.');
			}
			else if ($this->cost < $object->cost) {
				return -1;
			}
			else if ($this->cost > $object->cost) {
				return 1;
			}
			else {
				return 0;
			}
		}

		/**
		 * This method returns the actions associated with the transition.
		 *
		 * @access public
		 * @return \Unicity\Common\IList                            a list of all actions associated with
		 *                                                          the transition
		 */
		public function getActions() {
			return $this->actions;
		}

		/**
		 * This method returns the cost (sometimes referred to as the weight) assigned to the transition.
		 *
		 * @access public
		 * @return double                                           the cost assigned to the transition
		 */
		public function getCost() {
			return $this->cost;
		}

		/**
		 * This method returns the id associated with the transition.
		 *
		 * @access public
		 * @return string                                           the id associated with the transition
		 */
		public function getId() {
			if (empty($this->id)) {
				return $this->__hashCode();
			}
			return $this->id;
		}

		/**
		 * This method returns a set of target states resulting from the transition.
		 *
		 * @access public
		 * @return \Unicity\Common\Mutable\ISet                      a set of target states resulting
		 *                                                           from the transition
		 */
		public function getTargets() {
			return $this->targets;
		}

		/**
		 * This method determines whether the transition is traversable.
		 *
		 * @access public
		 * @param \Unicity\Common\IList $sigma                      the input alphabet/sequence
		 * @param integer $index                                    the index to the input symbol in
		 *                                                          the sigma
		 * @return boolean                                          whether the input symbol is
		 *                                                          accepted
		 *
		 * @see http://www.w3.org/TR/scxml/#transition
		 * @see http://www.w3.org/TR/scxml/#EventDescriptors
		 * @see http://www.w3.org/TR/scxml/#SelectingTransitions
		 */
		public function isTraversable(Common\IList $sigma, $index) {
			if (is_callable($this->condition)) {
				return (bool) call_user_func_array($this->condition, array($sigma, $index));
			}
			return ((string)serialize($sigma->getValue($index)) == (string)serialize($this->condition));
		}

		/**
		 * This method removes an action from the transition.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IAction $action                the action to be removed
		 */
		public function removeAction(Automaton\IAction $action) {
			if ($action !== null) {
				$this->actions->removeValue($action);
			}
		}

		/**
		 * This method removes a set of target states.
		 *
		 * @access public
		 * @param \Traversable $actions                             a set of actions to be removed
		 */
		public function removeActions($actions) {
			if ($actions !== null) {
				foreach ($actions as $action) {
					$this->removeAction($action);
				}
			}
		}

		/**
		 * This method removes a target state from the transition.
		 *
		 * @access public
		 * @param mixed $target                                     the target state to be removed
		 */
		public function removeTarget($target) {
			if ($target !== null) {
				$tid = ($target instanceof Automaton\IState) ? $target->getId() : '' . $target;
				$this->targets->removeValue($tid);
			}
		}

		/**
		 * This method removes a set of target states from the transition.
		 *
		 * @access public
		 * @param \Traversable $targets                             a set of target states to be
		 *                                                          removed
		 */
		public function removeTargets($targets) {
			if ($targets !== null) {
				foreach ($targets as $target) {
					$this->removeTarget($target);
				}
			}
		}

		/**
		 * This method sets the guard condition for the transition.  The condition may either be function
		 * (which accepts two arguments: (1) the sigma and (2) the index) or a value that will be used to
		 * evaluate against.
		 *
		 * @access public
		 * @param mixed $condition                                  the guard to associated with the
		 *                                                          transition
		 */
		public function setCondition($condition) {
			$this->condition = $condition;
		}

		/**
		 * This method sets the cost (sometimes referred to as the weight) for the transition.
		 *
		 * @access public
		 * @param double $cost                                      the cost to be assigned to the
		 *                                                          transition
		 */
		public function setCost($cost) {
			$this->cost = $cost;
		}

		/**
		 * This method adds the transition to the machine.
		 *
		 * @param \Unicity\Automaton\IMachine $machine              the state machine where the transition
		 *                                                          will be added
		 */
		public function setMachine(Automaton\IMachine $machine) {
			$machine->addTransition($this);
		}

	}

}