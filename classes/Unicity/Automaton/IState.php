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

namespace Unicity\Automaton {

	use \Unicity\Automaton;
	use \Unicity\Common;
	use \Unicity\Core;

	/**
	 * This interfaces defines the contract for a state in an automaton.
	 *
	 * @access public
	 * @interface
	 * @package Automaton
	 */
	interface IState extends Core\IComparable {

		/**
		 * This method adds the specified transition to the state.
		 *
		 * @access public
		 * @param mixed $transition                                 the transition to be added
		 */
		public function addTransition($transition);

		/**
		 * This method adds the specified transition to the state.
		 *
		 * @access public
		 * @param \Traverable $transitions                          a list of transitions to be added
		 */
		public function addTransitions($transitions);

		/**
		 * This method returns any constraint placed on the state.
		 *
		 * @access public
		 * @return mixed                                            any constraint placed on the state
		 */
		public function getConstraint();

		/**
		 * This method returns the id associated with the state.
		 *
		 * @access public
		 * @return string                                           the id associated with the state
		 */
		public function getId();

		/**
		 * This method returns the priority assigned to the state.
		 *
		 * @access public
		 * @return double                                           the priority assigned to the state
		 */
		public function getPriority();

		/**
		 * This method returns the transitions associated with the state.
		 *
		 * @access public
		 * @return \Unicity\Common\ISet                             the transitions associated with
		 *                                                          the state
		 */
		public function getTransitions();

		/**
		 * This method return the type assigned to the state.
		 *
		 * @access public
		 * @return \Unicity\Automaton\StateType                     the type assigned to the state
		 */
		public function getType();

		/**
		 * This method returns the value associated with the state.
		 *
		 * @access public
		 * @return mixed                                            the value associated with the
		 *                                                          state
		 */
		public function getValue();

		/**
		 * This method removes the specified transition from the state.
		 *
		 * @access public
		 * @param string $transition                                the transition to be removed
		 */
		public function removeTransition($transition);

		/**
		 * This method removes a set of transitions from the state.
		 *
		 * @access public
		 * @param \Traversable $transitions                         a list of transitions to be removed
		 */
		public function removeTransitions($transitions);

		/**
		 * This method sets a constraint on the state.
		 *
		 * @access public
		 * @param mixed $constraint                                 the constraint to be set
		 */
		public function setConstraint($constraint);

		/**
		 * This method adds the state to the machine.
		 *
		 * @param \Unicity\Automaton\IMachine $machine              the state machine where the state
		 *                                                          will be added
		 * @param boolean $initial                                  whether the state is considered an
		 *                                                          initial state
		 */
		public function setMachine(Automaton\IMachine $machine, $initial = false);

		/**
		 * This method returns the priority assigned to the state.
		 *
		 * @access public
		 * @param double $priority                                  the priority to be assigned
		 */
		public function setPriority($priority);

		/**
		 * This method sets the value associated with the state.
		 *
		 * @access public
		 * @param mixed $value                                      the value to be set
		 */
		public function setValue($value);

	}

}