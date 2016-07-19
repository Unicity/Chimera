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

namespace Unicity\Automaton {

	use \Unicity\Automaton;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This interface defines the contract for a transition in automaton.
	 *
	 * @access public
	 * @interface
	 * @package Automaton
	 */
	interface ITransition extends Core\IComparable {

		/**
		 * This method adds an action to the transition.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IAction $action                an action to be added to the
		 *                                                          transition
		 */
		public function addAction(Automaton\IAction $action);

		/**
		 * This method adds a list of actions to the transition.
		 *
		 * @access public
		 * @param mixed $actions                                    a list of actions to be added to
		 *                                                          the transition
		 */
		public function addActions($actions);

		/**
		 * This method adds a target state.
		 *
		 * @access public
		 * @param mixed $target                                     a target state to be added
		 */
		public function addTarget($target);

		/**
		 * This method adds a set of target states.
		 *
		 * @access public
		 * @param \Traversable $targets                              a set of target states to be
		 *                                                          added
		 */
		public function addTargets($targets);

		/**
		 * This method returns the actions associated with the transition.
		 *
		 * @access public
		 * @return \Unicity\Common\IList                            a list of all actions associated with
		 *                                                          the transition
		 */
		public function getActions();

		/**
		 * This method returns the cost (sometimes referred to as the weight) assigned to the transition.
		 *
		 * @access public
		 * @return double                                           the cost assigned to the transition
		 */
		public function getCost();

		/**
		 * This method returns the id associated with the transition.
		 *
		 * @access public
		 * @return string                                           the id associated with the transition
		 */
		public function getId();

		/**
		 * This method returns a set of target states resulting from the transition.
		 *
		 * @access public
		 * @return \Unicity\Common\Mutable\ISet                      a set of target states resulting
		 *                                                           from the transition
		 */
		public function getTargets();

		/**
		 * This method determines whether the transition is traversable.
		 *
		 * @access public
		 * @param \Unicity\Common\IList $sigma                      the input alphabet/sequence
		 * @param integer $index                                    the index to the input symbol in
		 *                                                          the sigma
		 * @return boolean                                          whether the input symbol is
		 *                                                          accepted
		 */
		public function isTraversable(Common\IList $sigma, $index);

		/**
		 * This method removes an action from the transition.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IAction $action                the action to be removed
		 */
		public function removeAction(Automaton\IAction $action);

		/**
		 * This method removes a set of target states.
		 *
		 * @access public
		 * @param \Traversable $actions                             a set of actions to be removed
		 */
		public function removeActions($actions);

		/**
		 * This method removes a target state from the transition.
		 *
		 * @access public
		 * @param mixed $target                                     the target state to be removed
		 */
		public function removeTarget($target);

		/**
		 * This method removes a set of target states from the transition.
		 *
		 * @access public
		 * @param \Traversable $targets                             a set of target states to be
		 *                                                          removed
		 */
		public function removeTargets($targets);

		/**
		 * This method sets the guard condition for the transition.  The condition may either be function
		 * (which accepts two arguments: (1) the sigma and (2) the index) or a value that will be used to
		 * evaluate against.
		 *
		 * @access public
		 * @param mixed $condition                                  the guard to associated with the
		 *                                                          transition
		 */
		public function setCondition($condition);

		/**
		 * This method sets the cost (sometimes referred to as the weight) for the transition.
		 *
		 * @access public
		 * @param double $cost                                      the cost to be assigned to the
		 *                                                          transition
		 */
		public function setCost($cost);

		/**
		 * This method adds the transition to the machine.
		 *
		 * @param \Unicity\Automaton\IMachine $machine              the state machine where the transition
		 *                                                          will be added
		 */
		public function setMachine(Automaton\IMachine $machine);

	}

}