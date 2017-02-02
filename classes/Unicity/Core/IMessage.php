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

namespace Unicity\Core {

	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This interface provides the contract for defining a message.
	 *
	 * @access public
	 * @interface
	 * @package Core
	 */
	interface IMessage {

		/**
		 * This method returns the message's body.
		 *
		 * @access public
		 * @return IO\File                                          the message's body
		 */
		public function getBody() : ?IO\File ;

		/**
		 * This method returns the message header mapped to the given name.
		 *
		 * @access public
		 * @param string $name                                      the name of the header
		 * @return string                                           the value of the header
		 */
		public function getHeader(string $name) : string;

		/**
		 * This method returns the message's id.
		 *
		 * @access public
		 * @return string                                           the message's id
		 */
		public function getMessageId() : string;
		/**
		 * This method sets the body with the contents in the standard input stream
		 * buffer.
		 *
		 * @access public
		 * @return IO\File                                          the message's body
		 */
		public function receive() : IO\File;

		/**
		 * This method sends the message.
		 *
		 * @access public
		 */
		public function send();

		/**
		 * This method sets the message's body.
		 *
		 * @access public
		 * @param mixed $body                                       the message's body
		 */
		public function setBody($body = null);

		/**
		 * This method sets the header with specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the header
		 * @param string $value                                     the value of the header
		 */
		public function setHeader(string $name, ?string $value);

		/**
		 * This method sets the headers with the specified name/value pairs.
		 *
		 * @access public
		 * @param array $headers                                    the headers associated with the message
		 */
		public function setHeaders(array $headers);

		/**
		 * This method sets the message's id.
		 *
		 * @access public
		 * @param string $id
		 * @throws Throwable\Parse\Exception                        the message id
		 */
		public function setMessageId(?string $id);

	}

}