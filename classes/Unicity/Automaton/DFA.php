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
	use \Unicity\Throwable;

	/**
	 * This class represents a deterministic finite automaton.
	 *
	 * @access public
	 * @class
	 * @package Automaton
	 */
	class DFA extends Automaton\Machine {

		/**
		 * This method runs the machine using the specified sigma (i.e. the input alphabet/sequence).
		 *
		 * @access public
		 * @param \Unicity\Common\IList $sigma                      the sigma to be processed
		 * @param \Unicity\Common\Mutable\IList $path               the path through which the pattern
		 *                                                          was found
		 * @return boolean                                          whether the machine finished in
		 *                                                          a goal state
		 * @throws \Unicity\Throwable\InvalidArgument\Exception     indicates that no sigma has been
		 *                                                          specified
		 * @throws \Unicity\Throwable\Parse\Exception               indicates that the machine failed
		 *                                                          to parse
		 */
		public function run(Common\IList $sigma, Common\Mutable\IList $path = null) {
			if (($sigma === null) || $sigma->isEmpty()) {
				throw new Throwable\InvalidArgument\Exception('No sigma has been defined.');
			}

			$count = $sigma->count();

			if (($this->initials === null) || $this->initials->isEmpty()) {
				throw new Throwable\Parse\Exception('Machine failed. No initial state has been defined.');
			}

			$stack = new Common\Mutable\Stack($path);

			$states = $this->states->getValues($this->initials);
			usort($states, function(Core\IComparable $c0, Core\IComparable $c1) {
				return $c0->compareTo($c1);
			});
			$state = $states[0];
			$stack->push($state->getId());
			$this->onStart($this, $state);

			for ($i = 0; $i < $count; $i++) {
				$transitions = $this->transitions->getValues($state->getTransitions());
				usort($transitions, function(Core\IComparable $c0, Core\IComparable $c1) {
					return $c0->compareTo($c1);
				});

				$hasTransitioned = false;

				foreach ($transitions as $transition) {
					if ($transition->isTraversable($sigma, $i)) {
						$this->onExit($this, $state);
						$this->onTransition($this, $transition);
						$targets = $this->states->getValues($transition->getTargets());
						usort($targets, function(Core\IComparable $c0, Core\IComparable $c1) {
							return $c0->compareTo($c1);
						});
						$state = $targets[0];
						$stack->push($state->getId());
						$this->onEntry($this, $state);
						$hasTransitioned = true;
						break;
					}
				}

				if (!$hasTransitioned) {
					$stack->clear();
					throw new Throwable\Parse\Exception('Machine failed. Unable to transition between states.');
				}
			}

			$this->onCompletion($this, $state);

			if (Automaton\StateType::goal()->__equals($state->getType())) {
				return true;
			}

			$stack->clear();
			return false;
		}

	}

}