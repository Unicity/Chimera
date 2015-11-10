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
	 * This class represents a finite automaton.
	 *
	 * @access public
	 * @class
	 * @package Automaton
	 */
	abstract class Machine extends Core\Object implements Automaton\IMachine {

		/**
		 * This variable stores the delegate for the machine.
		 *
		 * @access protected
		 * @var \Unicity\Automaton\IMachineDelegate
		 */
		protected $delegate;

		/**
		 * This variable stores the initial states for the machine.
		 *
		 * @access protected
		 * @var \Unicity\Common\Mutable\ISet
		 */
		protected $initials;

		/**
		 * This variable stores a collection of states for the machine.
		 *
		 * @access protected
		 * @var \Unicity\Common\Mutable\IMap
		 */
		protected $states;

		/**
		 * This variable stores a collection of states for the machine.
		 *
		 * @access protected
		 * @var \Unicity\Common\Mutable\IMap
		 */
		protected $transitions;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachineDelegate $delegate     the delegate for the machine
		 */
		public function __construct(Automaton\IMachineDelegate $delegate = null) {
			$this->delegate = $delegate;
			$this->initials = new Common\Mutable\HashSet();
			$this->states = new Common\Mutable\HashMap();
			$this->transitions = new Common\Mutable\HashMap();
		}

		/**
		 * This method adds a state to the machine.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IState $state                  the state to be added
		 * @param boolean $initial                                  whether the state is considered an
		 *                                                          initial state
		 */
		public function addState(Automaton\IState $state, $initial = false) {
			if ($state !== null) {
				$id = $state->getId();
				if ($initial) {
					$this->initials->putValue($id);
				}
				$this->states->putEntry($id, $state);
			}
		}

		/**
		 * This method adds a transition to the machine.
		 *
		 * @access public
		 * @param \Unicity\Automaton\ITransition $transition        the transition to be added
		 */
		public function addTransition(Automaton\ITransition $transition) {
			if ($transition !== null) {
				$this->transitions->putEntry($transition->getId(), $transition);
			}
		}

		/**
		 * This method returns the delegate for the machine.
		 *
		 * @access public
		 * @return \Unicity\Automaton\IMachineDelegate              the delegate for the machine
		 */
		public function getDelegate() {
			return $this->delegate;
		}

		/**
		 * This method returns the state with the specified id.
		 *
		 * @access public
		 * @param string $id                                        the id of the state to be
		 *                                                          returned
		 * @return \Unicity\Automaton\IState                        the state with the specified id
		 */
		public function getStateWithId($id) {
			return $this->states->getValue($id);
		}

		/**
		 * This method returns the transition with the specified id.
		 *
		 * @access public
		 * @param string $id                                        the id of the transition to be
		 *                                                          returned
		 * @return \Unicity\Automaton\ITransition                   the transition with the specified id
		 */
		public function getTransitionWithId($id) {
			return $this->transitions->getValue($id);
		}

		/**
		 * This method removes a state from the machine.
		 *
		 * @access public
		 * @param string $id                                        the id of the state to be
		 *                                                          removed
		 */
		public function removeStateWithId($id) {
			if ($this->states->hasKey($id)) {
				$this->states->removeKey($id);
			}
		}

		/**
		 * This method removes a transition from the machine.
		 *
		 * @access public
		 * @param string $id                                        the id of the transition to be
		 *                                                          removed
		 */
		public function removeTransitionWithId($id) {
			if ($this->transitions->hasKey($id)) {
				$this->transitions->removeKey($id);
			}
		}

		/**
		 * This method runs the machine using the specified sigma (i.e. the input alphabet/sequence).
		 *
		 * @access public
		 * @param \Unicity\Common\IList $sigma                      the sigma to be processed
		 * @param \Unicity\Common\Mutable\IList $path               the path through which the pattern
		 *                                                          was found
		 * @return boolean                                          whether the machine finished in
		 *                                                          a goal state
		 * @throws Throwable\UnimplementedMethod\Exception          indicates this method has not been
		 *                                                          implemented
		 */
		public function run(Common\IList $sigma, Common\Mutable\IList $path = null) {
			throw new Throwable\UnimplementedMethod\Exception('Unable to run machine. This method has not been implemented.');
		}

		/**
		 * This method sets the delegate for the machine.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachineDelegate $delegate     the delegate for the machine
		 */
		public function setDelegate(Automaton\IMachineDelegate $delegate) {
			$this->delegate = $delegate;
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This method is called when a machine is finished running.
		 *
		 * @access protected
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		protected function onCompletion(Automaton\IMachine $machine, Automaton\IState $state) {
			if ($this->delegate !== null) {
				$this->delegate->onCompletion($machine, $state);
			}
		}

		/**
		 * This method is called when a state is entered.
		 *
		 * @access protected
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		protected function onEntry(Automaton\IMachine $machine, Automaton\IState $state) {
			if ($this->delegate !== null) {
				$this->delegate->onEntry($machine, $state);
			}
		}

		/**
		 * This method is called when a state is exited.
		 *
		 * @access protected
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		protected function onExit(Automaton\IMachine $machine, Automaton\IState $state) {
			if ($this->delegate !== null) {
				$this->delegate->onExit($machine, $state);
			}
		}

		/**
		 * This method is called when a machine starts to run.
		 *
		 * @access protected
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		protected function onStart(Automaton\IMachine $machine, Automaton\IState $state) {
			if ($this->delegate !== null) {
				$this->delegate->onStart($machine, $state);
			}
		}

		/**
		 * This method is called when a transition is traversed.
		 *
		 * @access protected
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\ITransition $transition        the current transition
		 */
		protected function onTransition(Automaton\IMachine $machine, Automaton\ITransition $transition) {
			if ($this->delegate !== null) {
				$this->delegate->onTransition($machine, $transition);
			}
		}

	}

}