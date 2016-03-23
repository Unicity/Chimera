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

namespace Unicity\BT {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	//use \Unicity\Log;
	use Unicity\MappingService\Data\Model\Entity;

	/**
	 * This class represents an exchange.
	 *
	 * @access public
	 * @class
	 * @see http://www.gamedev.net/page/resources/_/technical/game-programming/understanding-component-entity-systems-r3013
	 * @see http://www.gamedev.net/page/resources/_/technical/game-programming/implementing-component-entity-systems-r3382
	 */
	class Application extends Core\Object {

		/**
		 * This variable stores a map of id/entity pairs.
		 *
		 * @access protected
		 * @var array
		 */
		protected $entities;

		/**
		 * This variable stores a reference to the log writer.
		 *
		 * @access protected
		 * @var Log\Manager
		 */
		protected $log;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct(/*Log\Manager*/ $log = null) {
			$this->entities = new Common\Mutable\HashMap();
			$this->log = $log;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->entities);
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @param string $entityId                                  the id of the entity
		 * @return BT\Entity                                        the entity
		 */
		public function getEntity($entityId) {
			return $this->entities->getValue($entityId);
		}

		/**
		 * This method returns an array list of entities.
		 *
		 * @access public
		 * @return Common\ArrayList                                 an array list of entities
		 */
		public function getEntities() {
			return new Common\ArrayList($this->entities->getValues());
		}

		/**
		 * This method returns a reference to a log manager.
		 *
		 * @access public
		 * @return Log\Manager                                      a reference to a log manager
		 */
		public function getLog() {
			return $this->log;
		}

		/**
		 * This method creates an entity id.
		 *
		 * @access public
		 * @static
		 * @param Application $application                          the application for which the entity
		 *                                                          is being created
		 * @return entity                                           the entity id
		 */
		public static function createEntity(BT\Application $application) {
			$entityId = 0;
			while ($application->entities->hasKey($entityId)) {
				$entityId++;
			}
			$entity = new Entity($entityId);
			$application->entities->putEntry($entityId, $entityId, $application);
			return $entityId;
		}

		/**
		 * This method removes an entity id.
		 *
		 * @access public
		 * @static
		 * @param Application $application
		 * @param integer $entityId
		 */
		public static function destroyEntity(BT\Application $application, $entityId) {
			unset($application->entities[$entityId]);
		}

	}

}