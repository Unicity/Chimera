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
use Unicity\Core;
use Unicity\Throwable;

/**
 * This class represents a state in an automaton.
 *
 * @access public
 * @class
 * @package Automaton
 */
class State extends Core\AbstractObject implements IState
{
    /**
     * This variable stores any constraint placed on the state.
     *
     * @access protected
     * @var mixed
     */
    protected $constraint;

    /**
     * This variable stores the id associated with the state.
     *
     * @access protected
     * @var string
     */
    protected $id;

    /**
     * This variable stores the priority of the state.
     *
     * @access protected
     * @var double
     */
    protected $priority;

    /**
     * This variable stores the transitions associated with the state.
     *
     * @access protected
     * @var \Unicity\Common\Mutable\ISet
     */
    protected $transitions;

    /**
     * This variable stores the type of state.
     *
     * @access protected
     * @var \Unicity\Automaton\StateType
     */
    protected $type;

    /**
     * This variable stores the value associated with the state.
     *
     * @access protected
     * @var mixed
     */
    protected $value;

    /**
     * This constructor initializes the class using the specified options.
     *
     * @access public
     * @param string $id the id given to the state
     * @param \Unicity\Automaton\StateType $type the type of state
     * @param mixed $value the value associated with the state
     * @param mixed $constraint a constraint on the state
     * @param double $priority the priority given to the state
     */
    public function __construct($id, Automaton\StateType $type, $value = null, $constraint = null, $priority = 0.0)
    {
        $this->constraint = $constraint;
        $this->id = $id;
        $this->priority = $priority;
        $this->transitions = new Common\Mutable\HashSet();
        $this->type = ($type !== null) ? $type : Automaton\StateType::normal();
        $this->value = $value;
    }

    /**
     * This method adds the specified transition to the state.
     *
     * @access public
     * @param mixed $transition the transition to be added
     */
    public function addTransition($transition)
    {
        if ($transition !== null) {
            $tid = ($transition instanceof Automaton\ITransition) ? $transition->getId() : '' . $transition;
            $this->transitions->putValue($tid);
        }
    }

    /**
     * This method adds the specified transition to the state.
     *
     * @access public
     * @param \Traverable $transitions a list of transitions to be added
     */
    public function addTransitions($transitions)
    {
        if ($transitions !== null) {
            foreach ($transitions as $transition) {
                $this->addTransition($transition);
            }
        }
    }

    /**
     * This method compares the specified object with the current object for order.
     *
     * @access public
     * @param \Unicity\Automaton\IState $object the object to be compared
     * @return integer a negative integer, zero, or a positive
     *                 integer as this object is less than,
     *                 equal to, or greater than the specified
     *                 object
     * @throws \Unicity\Throwable\InvalidArgument\Exception indicates that the object must be of the
     *                                                      same type
     */
    public function compareTo($object): int
    {
        if (!($object instanceof Automaton\IState)) {
            throw new Throwable\InvalidArgument\Exception('Invalid comparison. Object must be an instance of IState.');
        } elseif ($this->priority < $object->priority) {
            return -1;
        } elseif ($this->priority > $object->priority) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * This method returns any constraint placed on the state.
     *
     * @access public
     * @return mixed any constraint placed on the state
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * This method returns the id associated with the state.
     *
     * @access public
     * @return string the id associated with the state
     */
    public function getId()
    {
        if (empty($this->id)) {
            return $this->__hashCode();
        }

        return $this->id;
    }

    /**
     * This method returns the priority assigned to the state.
     *
     * @access public
     * @return double the priority assigned to the state
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * This method returns the transitions associated with the state.
     *
     * @access public
     * @return \Unicity\Common\ISet the transitions associated with
     *                              the state
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * This method return the type assigned to the state.
     *
     * @access public
     * @return \Unicity\Automaton\StateType the type assigned to the state
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This method returns the value associated with the state.
     *
     * @access public
     * @return mixed the value associated with the
     *               state
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * This method removes the specified transition from the state.
     *
     * @access public
     * @param string $transition the transition to be removed
     */
    public function removeTransition($transition)
    {
        if ($transition !== null) {
            $tid = ($transition instanceof Automaton\ITransition) ? $transition->getId() : '' . $transition;
            $this->transitions->removeValue($tid);
        }
    }

    /**
     * This method removes a set of transitions from the state.
     *
     * @access public
     * @param $transitions a list of transitions to be removed
     */
    public function removeTransitions($transitions)
    {
        if ($transitions !== null) {
            foreach ($transitions as $transition) {
                $this->removeTransition($transition);
            }
        }
    }

    /**
     * This method sets a constraint on the state.
     *
     * @access public
     * @param mixed $constraint the constraint to be set
     */
    public function setConstraint($constraint)
    {
        $this->constraint = $constraint;
    }

    /**
     * This method adds the state to the machine.
     *
     * @param \Unicity\Automaton\IMachine $machine the state machine where the state
     *                                             will be added
     * @param boolean $initial whether the state is considered an
     *                         initial state
     */
    public function setMachine(Automaton\IMachine $machine, $initial = false)
    {
        $machine->addState($this, $initial);
    }

    /**
     * This method returns the priority assigned to the state.
     *
     * @access public
     * @param double $priority the priority to be assigned
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * This method sets the value associated with the state.
     *
     * @access public
     * @param mixed $value the value to be set
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

}
