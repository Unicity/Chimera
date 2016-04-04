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

	/**
	 * This class represents a behavior tree engine.
	 *
	 * @access public
	 * @class
	 * @see http://www.gamedev.net/page/resources/_/technical/game-programming/understanding-component-entity-systems-r3013
	 * @see http://www.gamedev.net/page/resources/_/technical/game-programming/implementing-component-entity-systems-r3382
	 */
	class Engine extends Core\Object {

		/**
		 * This variable stores a map of blackboards.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $blackboards;

		/**
		 * This variable stores a map of id/entity pairs.
		 *
		 * @access protected
		 * @var Common\Mutable\IMap
		 */
		protected $entities;

		/**
		 * This variable stores a reference to the error log.
		 *
		 * @access protected
		 * @var Log\Manager
		 */
		protected $log;

		/**
		 * This variable stores a reference to the response message.
		 *
		 * @access protected
		 * @var Core\Message
		 */
		protected $response;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param Common\ArrayList $entities                        a list of entities to be added
		 */
		public function __construct(Common\ArrayList $entities = null) {
			$this->blackboards = new Common\Mutable\HashMap();
			$this->entities = new Common\Mutable\HashMap();
			if ($entities !== null) {
				foreach ($entities as $entity) {
					$this->putEntity($entity);
				}
			}
			$this->log = null; // TODO set a reference to the error log
			$this->response = new Core\Message();
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->blackboards);
			unset($this->entities);
			unset($this->log);
			unset($this->response);
		}

		/**
		 * This method returns a reference to a blackboard matching the specified id.
		 *
		 * @access public
		 * @param string $bbId                                      the blackboard's id
		 * @return Common\Mutable\IMap                              a reference to the blackboard
		 */
		public function getBlackboard(string $bbId = 'global') {
			if (!$this->blackboards->hasKey($bbId)) {
				$this->blackboards->putEntry($bbId, new Common\Mutable\HashMap());
			}
			return $this->blackboards->getValue($bbId);
		}

		/**
		 * This method returns the value associated with the specified key.
		 *
		 * @access public
		 * @param string $entityId                                  the id of the entity
		 * @return BT\Entity                                        the entity
		 */
		public function getEntity(string $entityId) {
			return $this->entities->getValue($entityId);
		}

		/**
		 * This method returns an array list of entities.
		 *
		 * @access public
		 * @return Common\ArrayList                                 an array list of entities
		 */
		public function getEntities() {
			return $this->entities->toList();
		}

		/**
		 * This method returns a reference to the error log.
		 *
		 * @access public
		 * @return Log\Manager                                      a reference to the error log
		 */
		public function getErrorLog() {
			return $this->log;
		}

		/**
		 * This method returns a reference to the response object.
		 *
		 * @access public
		 * @return Core\Message                                     a reference to the response
		 *                                                          object
		 */
		public function getResponse() {
			return $this->response;
		}

		/**
		 * This method returns a reference to the request object.
		 *
		 * @access public
		 * @return Core\Message                                     a reference to the request
		 *                                                          object
		 */
		public function getRequest() {
			return null; // TODO return a request object
		}

		/**
		 * This method notifies all entities with a message using the specified handler.
		 *
		 * @access public
		 * @param callable $handler                                 the handler to be called
		 * @param mixed $message                                    the message to be passed
		 */
		public function notifyAll(callable $handler, $message = null) {
			foreach ($this->entities as $entity) {
				$entity->notify($handler, $message);
			}
		}

		/**
		 * This method adds the given entity.
		 *
		 * @access public
		 * @param BT\Entity $entity                                 the entity to be added
		 */
		public function putEntity(BT\Entity $entity) {
			$this->entities->putEntry($entity->getId(), $entity);
		}

		/**
		 * This method removes the blackboard matching the given id.
		 *
		 * @access public
		 * @param string $bbId                                      the id of the blackboard to be
		 *                                                          removed
		 */
		public function removeBlackboard(string $bbId) {
			$this->blackboards->removeKey($bbId);
		}

		/**
		 * This method removes the given entity.
		 *
		 * @access public
		 * @static
		 * @param BT\Entity $entity                                 the entity to be removed
		 */
		public function removeEntity(BT\Entity $entity) {
			$this->entities->removeKey($entity->getId());
		}

	}

}