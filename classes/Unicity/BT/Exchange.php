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
	use \Unicity\Core;

	/**
	 * This class represents an exchange.
	 *
	 * @access public
	 * @class
	 * @see http://camel.apache.org/maven/current/camel-core/apidocs/org/apache/camel/Exchange.html
	 */
	class Exchange extends Core\Object {

		/**
		 * This variable stores a reference to the inbound message.
		 *
		 * @access protected
		 * @var BT\Message
		 */
		protected $inbound;

		/**
		 * This variable stores a reference to the outbound message.
		 *
		 * @access protected
		 * @var BT\Message
		 */
		protected $outbound;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->inbound = new BT\Message();
			$this->outbound = new BT\Message();
		}

		/**
		 * This method returns the inbound message.
		 *
		 * @access public
		 * @return BT\Message                                       the inbound message
		 */
		public function getIn() {
			return $this->inbound;
		}

		/**
		 * This method returns the outbound message.
		 *
		 * @access public
		 * @return BT\Message                                       the outbound message
		 */
		public function getOut() {
			return $this->outbound;
		}

		/**
		 * This method sets the inbound message.
		 *
		 * @access public
		 * @param BT\Message $message                               the inbound message to be set
		 */
		public function setIn(BT\Message $message) {
			$this->inbound = $message;
		}

		/**
		 * This method sets the outbound message.
		 *
		 * @access public
		 * @param BT\Message $message                               the outbound message to be set
		 */
		public function setOut(BT\Message $message) {
			$this->outbound = $message;
		}

	}

}