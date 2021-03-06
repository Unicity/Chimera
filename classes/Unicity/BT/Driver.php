<?php

declare(strict_types = 1);

namespace Unicity\BT {

	use \Unicity\BT;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Spring;

	class Driver extends Core\AbstractObject {

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
		 * @param IO\File $file                                     the file to be processed
		 */
		public function __construct(IO\File $file) {
			$this->file = $file;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->file);
		}

		/**
		 * This method executes the tasks define in the pipeline.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine to be run
		 * @return integer                                          the status
		 */
		public function run(BT\Engine $engine) {
			$factory = new Spring\XMLObjectFactory(Spring\Data\XML::load($this->file));

			$registry = $factory->getParser()->getRegistry();
			$registry->putEntry(array('action', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ActionElement());
			$registry->putEntry(array('branch', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\BranchElement());
			$registry->putEntry(array('composite', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\CompositeElement());
			$registry->putEntry(array('concurrent', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ConcurrentElement());
			$registry->putEntry(array('condition', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ConditionElement());
			$registry->putEntry(array('decorator', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\DecoratorElement());
			$registry->putEntry(array('entry', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\EntryElement);
			$registry->putEntry(array('guard', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\GuardElement());
			$registry->putEntry(array('leaf', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\LeafElement());
			$registry->putEntry(array('logger', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\LoggerElement());
			$registry->putEntry(array('parallel', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ParallelElement());
			$registry->putEntry(array('picker', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\PickerElement());
			$registry->putEntry(array('ref', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\RefElement());
			$registry->putEntry(array('resetter', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ResetterElement());
			$registry->putEntry(array('responder', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ResponderElement());
			$registry->putEntry(array('selector', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SelectorElement());
			$registry->putEntry(array('semaphore', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SemaphoreElement());
			$registry->putEntry(array('sequence', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SequenceElement());
			$registry->putEntry(array('policy', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\PolicyElement());
			$registry->putEntry(array('stub', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\StubElement());
			$registry->putEntry(array('tasks', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TasksElement());
			$registry->putEntry(array('ticker', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TickerElement());
			$registry->putEntry(array('timer', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TimerElement());

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
			}
			while ($active > 0);

			return BT\Status::QUIT;
		}

	}

}