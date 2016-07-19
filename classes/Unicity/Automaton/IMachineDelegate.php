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

	/**
	 * This interfaces defines the contract for monitoring an automaton.
	 *
	 * @access public
	 * @interface
	 * @package Automaton
	 */
	interface IMachineDelegate {

		/**
		 * This method is called when a machine is finished running.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		public function onCompletion(Automaton\IMachine $machine, Automaton\IState $state);

		/**
		 * This method is called when a state is entered.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		public function onEntry(Automaton\IMachine $machine, Automaton\IState $state);

		/**
		 * This method is called when a state is exited.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		public function onExit(Automaton\IMachine $machine, Automaton\IState $state);

		/**
		 * This method is called when a machine starts to run.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\IState $state                  the current state
		 */
		public function onStart(Automaton\IMachine $machine, Automaton\IState $state);

		/**
		 * This method is called when a transition is traversed.
		 *
		 * @access public
		 * @param \Unicity\Automaton\IMachine $machine              the machine
		 * @param \Unicity\Automaton\ITransition $transition        the current transition
		 */
		public function onTransition(Automaton\IMachine $machine, Automaton\ITransition $transition);

	}

}