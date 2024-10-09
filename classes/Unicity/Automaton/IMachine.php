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

declare(strict_types=1);

namespace Unicity\Automaton;

use Unicity\Automaton;
use Unicity\Common;

/**
 * This interface defines the contract for an automaton.
 *
 * @access public
 * @interface
 * @package Automaton
 */
interface IMachine
{
    /**
     * This method adds a state to the machine.
     *
     * @access public
     * @param \Unicity\Automaton\IState $state the state to be added
     * @param boolean $initial whether the state is considered an
     *                         initial state
     */
    public function addState(Automaton\IState $state, $initial = false);

    /**
     * This method adds a transition to the machine.
     *
     * @access public
     * @param \Unicity\Automaton\ITransition $transition the transition to be added
     */
    public function addTransition(Automaton\ITransition $transition);

    /**
     * This method returns the delegate for the machine.
     *
     * @access public
     * @return \Unicity\Automaton\IMachineDelegate the delegate for the machine
     */
    public function getDelegate();

    /**
     * This method returns the state with the specified id.
     *
     * @access public
     * @param string $id the id of the state to be
     *                   returned
     * @return \Unicity\Automaton\IState the state with the specified id
     */
    public function getStateWithId($id);

    /**
     * This method returns the transition with the specified id.
     *
     * @access public
     * @param string $id the id of the transition to be
     *                   returned
     * @return \Unicity\Automaton\ITransition the transition with the specified id
     */
    public function getTransitionWithId($id);

    /**
     * This method removes a state from the machine.
     *
     * @access public
     * @param string $id the id of the state to be
     *                   removed
     */
    public function removeStateWithId($id);

    /**
     * This method removes a transition from the machine.
     *
     * @access public
     * @param string $id the id of the transition to be
     *                   removed
     */
    public function removeTransitionWithId($id);

    /**
     * This method runs the machine using the specified sigma (i.e. the input alphabet/sequence).
     *
     * @access public
     * @param \Unicity\Common\IList $sigma the sigma to be processed
     * @param \Unicity\Common\Mutable\IList $path the path through which the pattern
     *                                            was found
     * @return boolean whether the machine finished in
     *                 a goal state
     * @throws \Unicity\Throwable\InvalidArgument\Exception indicates that no sigma has been
     *                                                      specified
     * @throws \Unicity\Throwable\Parse\Exception indicates that the machine failed
     *                                            to parse
     */
    public function run(Common\IList $sigma, Common\Mutable\IList $path = null);

    /**
     * This method sets the delegate for the machine.
     *
     * @access public
     * @param \Unicity\Automaton\IMachineDelegate $delegate the delegate for the machine
     */
    public function setDelegate(Automaton\IMachineDelegate $delegate);

}
