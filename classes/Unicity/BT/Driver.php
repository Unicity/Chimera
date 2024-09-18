<?php

declare(strict_types=1);

namespace Unicity\BT;

use Unicity\BT;
use Unicity\Core;
use Unicity\IO;
use Unicity\Spring;

class Driver extends Core\AbstractObject
{
    /**
     * This variable stores a reference to the file.
     *
     * @access protected
     * @var IO\File
     */
    protected $file;

    /**
     * This constructor initializes the class with the specified resource.
     *
     * @access public
     * @param IO\File $file the file to be processed
     */
    public function __construct(IO\File $file)
    {
        $this->file = $file;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->file);
    }

    /**
     * This method executes the tasks define in the pipeline.
     *
     * @access public
     * @param BT\Engine $engine the engine to be run
     * @return integer the status
     */
    public function run(BT\Engine $engine)
    {
        $factory = new Spring\XMLObjectFactory(Spring\Data\XML::load($this->file));

        $registry = $factory->getParser()->getRegistry();
        $registry->putEntry(['action', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ActionElement());
        $registry->putEntry(['branch', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\BranchElement());
        $registry->putEntry(['composite', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\CompositeElement());
        $registry->putEntry(['concurrent', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ConcurrentElement());
        $registry->putEntry(['condition', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ConditionElement());
        $registry->putEntry(['decorator', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\DecoratorElement());
        $registry->putEntry(['entry', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\EntryElement());
        $registry->putEntry(['guard', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\GuardElement());
        $registry->putEntry(['leaf', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\LeafElement());
        $registry->putEntry(['logger', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\LoggerElement());
        $registry->putEntry(['parallel', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ParallelElement());
        $registry->putEntry(['picker', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\PickerElement());
        $registry->putEntry(['ref', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\RefElement());
        $registry->putEntry(['resetter', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ResetterElement());
        $registry->putEntry(['responder', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\ResponderElement());
        $registry->putEntry(['selector', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\SelectorElement());
        $registry->putEntry(['semaphore', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\SemaphoreElement());
        $registry->putEntry(['sequence', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\SequenceElement());
        $registry->putEntry(['policy', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\PolicyElement());
        $registry->putEntry(['stub', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\StubElement());
        $registry->putEntry(['tasks', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\TasksElement());
        $registry->putEntry(['ticker', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\TickerElement());
        $registry->putEntry(['timer', BT\Schema::NAMESPACE_URI], new BT\Object\Factory\TimerElement());

        do {
            $active = 0;
            $entities = $engine->getEntities();
            foreach ($entities as $entity) {
                $taskId = $entity->getTaskId();
                if ($taskId !== null) {
                    $status = BT\Task\Handler::process($factory->getObject($taskId), $engine, $entity->getId());
                    switch ($status) {
                        case BT\Status::SUCCESS:
                        case BT\Status::FAILED:
                        case BT\Status::INACTIVE:
                            $entity->setTaskId(null);

                            break;
                        case BT\Status::ACTIVE:
                            $active++;

                            break;
                        case BT\Status::ERROR:
                        case BT\Status::QUIT:
                            return $status;
                    }
                }
            }
        } while ($active > 0);

        return BT\Status::QUIT;
    }

}
