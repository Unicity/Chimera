<?php

declare(strict_types=1);

namespace Unicity\EVT;

use Unicity\Core;
use Unicity\EVT;

abstract class AggregationStrategy extends Core\AbstractObject
{
    /**
     * This variable stores a reference to the old exchange.
     *
     * @access protected
     * @var EVT\Exchange
     */
    protected $oldExchange;

    /**
     * This constructor initializes the class.
     *
     * @access public
     */
    public function __construct()
    {
        $this->oldExchange = new EVT\Exchange();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->oldExchange);
    }

    /**
     * This method invoke the callback methods.
     *
     * @access public
     * @param mixed $message the message to be processed
     * @param EVT\Context $context the message's context
     */
    final public function __invoke($message, EVT\Context $context)
    {
        $newExchange = new EVT\Exchange([
            'context' => $context,
            'message' => $message,
        ]);

        if ($this->isMatch($newExchange)) {
            $this->oldExchange = $this->aggregate($this->oldExchange, $newExchange);
        }

        if ($this->isComplete($this->oldExchange)) {
            $this->onCompletion($this->oldExchange);
        }
    }

    /**
     * This method aggregates two exchanges.
     *
     * @access public
     * @param EVT\Exchange $oldExchange the old exchange
     * @param EVT\Exchange $newExchange the new exchange
     * @return EVT\Exchange the aggregated exchange
     */
    abstract public function aggregate(EVT\Exchange $oldExchange, EVT\Exchange $newExchange): EVT\Exchange;

    /**
     * This method returns whether the aggregation has completed.
     *
     * @access public
     * @param EVT\Exchange $exchange the exchanges to be evaluated
     * @return bool whether the aggregation has completed
     */
    abstract public function isComplete(EVT\Exchange $exchange): bool;

    /**
     * This method returns whether the exchange matches the aggregation strategy.
     *
     * @access public
     * @param EVT\Exchange $exchange the exchanges to be evaluated
     * @return bool whether the exchange matches the
     *              aggregation strategy
     */
    public function isMatch(EVT\Exchange $exchange): bool
    {
        return true;
    }

    /**
     * This method is executed upon completion of the aggregation strategy.
     *
     * @access public
     * @param Exchange $exchange the exchange to be processed
     */
    abstract public function onCompletion(EVT\Exchange $exchange): void;

}
