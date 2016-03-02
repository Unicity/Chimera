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

namespace Unicity\BT {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class Driver extends Core\Object {

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
		 * @param BT\Entity $entity                                 the entity to be processed
		 * @param string $id                                        the id of behavior tree to run
		 * @return BT\State                                         the state
		 */
		public function run(BT\Entity $entity, $id = 'BEHAVE') { // http://aigamedev.com/open/article/popular-behavior-tree-design/
			$factory = new Spring\XMLObjectFactory(Spring\Data\XML::load($this->file));

			$registry = $factory->getParser()->getRegistry();
			$registry->putEntry(array('action', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ActionElement());
			$registry->putEntry(array('blackboard', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\BlackboardElement());
			$registry->putEntry(array('branch', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\BranchElement());
			$registry->putEntry(array('breakpoint', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\BreakpointElement());
			$registry->putEntry(array('composite', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\CompositeElement());
			$registry->putEntry(array('condition', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ConditionElement());
			$registry->putEntry(array('decorator', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\DecoratorElement());
			$registry->putEntry(array('entry', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\EntryElement);
			$registry->putEntry(array('leaf', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\LeafElement());
			$registry->putEntry(array('logger', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\LoggerElement());
			$registry->putEntry(array('parallel', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ParallelElement());
			$registry->putEntry(array('picker', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\PickerElement());
			$registry->putEntry(array('ref', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\RefElement());
			$registry->putEntry(array('resetter', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\ResetterElement());
			$registry->putEntry(array('selector', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SelectorElement());
			$registry->putEntry(array('semaphore', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SemaphoreElement());
			$registry->putEntry(array('sequence', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\SequenceElement());
			$registry->putEntry(array('policy', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\PolicyElement());
			$registry->putEntry(array('stub', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\StubElement());
			$registry->putEntry(array('tasks', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TasksElement());
			$registry->putEntry(array('ticker', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TickerElement());
			$registry->putEntry(array('timer', BT\Schema::NAMESPACE_URI), new BT\Object\Factory\TimerElement());

			if ($factory->hasObject($id)) {
				$object = $factory->getObject($id);
				if ($object instanceof BT\Task) {
					return BT\Task\Handler::process($object, $entity);
				}
			}

			return BT\State\Error::with($entity);
		}

	}

}