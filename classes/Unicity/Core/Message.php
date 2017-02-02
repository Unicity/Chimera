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

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class represent a message.
	 *
	 * @access public
	 * @class
	 * @package Core
	 */
	class Message extends Core\Object implements Core\IMessage {

		/**
		 * This variable stores a reference to the singleton instance.
		 *
		 * @access protected
		 * @var Core\IMessage
		 */
		protected static $instance;

		/**
		 * This variable stores the HTTP status codes and descriptions.
		 *
		 * @access protected
		 * @var array
		 */
		protected static $statuses = array(
			// Informational 1xx
			100 => 'Continue',
			101 => 'Switching Protocols',

			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found', // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',

			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);

		/**
		 * This variable stores the message's body.
		 *
		 * @access protected
		 * @var IO\File
		 */
		protected $body;

		/**
		 * This variable stores the message's headers.
		 *
		 * @access protected
		 * @var Common\Mutable\HashMap
		 */
		protected $headers;

		/**
		 * This variable stores the message's id.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $id;

		/**
		 * This variable stores the HTTP protocol.
		 *
		 * @access protected
		 * @var string
		 */
		protected $protocol;

		/**
		 * This variable stores the HTTP status code.
		 *
		 * @access protected
		 * @var integer
		 */
		protected $status;

		/**
		 * This constructor initializes the class with some default data.
		 *
		 * @access public
		 * @param mixed $body                                       the message's body
		 */
		public function __construct($body = null) {
			$this->headers = new Common\Mutable\HashMap();
			$this->headers->putEntry('content-disposition', 'inline');
			$this->headers->putEntry('content-type', 'text/plain; charset=UTF-8');
			$this->headers->putEntry('cache-control', 'no-store, no-cache, must-revalidate');
			$this->headers->putEntry('expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
			$this->headers->putEntry('pragma', 'no-cache');
			$this->body = $body;
			$this->id = $this->__hashCode();
			$this->protocol = 'HTTP/1.1';
			$this->status = 200;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->body);
			unset($this->headers);
			unset($this->id);
			unset($this->protocol);
			unset($this->status);
		}

		/**
		 * This method returns the message's body.
		 *
		 * @access public
		 * @return IO\File                                          the message's body
		 */
		public function getBody() : ?IO\File {
			return $this->body;
		}

		/**
		 * This method returns the message's length.
		 *
		 * @access public
		 * @return integer                                          the message's length
		 */
		public function getLength() : int {
			if ($this->body !== null) {
				return $this->body->getFileSize();
			}
			return 0;
		}

		/**
		 * This method returns the message header mapped to the given name.
		 *
		 * @access public
		 * @param string $name                                      the name of the header
		 * @return string                                           the value of the header
		 */
		public function getHeader(string $name) : string {
			$name = strtolower($name);
			if ($this->headers->hasKey($name)) {
				return $this->headers->getValue($name);
			}
			return '';
		}

		/**
		 * This method returns the message's id.
		 *
		 * @access public
		 * @return string                                           the message's id
		 */
		public function getMessageId() : string {
			return $this->id;
		}

		/**
		 * This method returns the HTTP protocol.
		 *
		 * @access public
		 * @return string                                           the HTTP protocol
		 */
		public function getProtocol() : string {
			return $this->protocol;
		}

		/**
		 * This method returns the HTTP status code.
		 *
		 * @access public
		 * @return integer                                          the HTTP status code
		 */
		public function getStatus() : int {
			return $this->status;
		}

		/**
		 * This method sets the body with the contents in the standard input stream
		 * buffer.
		 *
		 * @access public
		 * @return IO\File                                          the message's body
		 */
		public function receive() : IO\File {
			$this->body = new IO\InputBuffer();
			return $this->body;
		}

		/**
		 * This method sends the message.
		 *
		 * @access public
		 */
		public function send() {
			header(implode(' ', array($this->protocol, $this->status, static::$statuses[$this->status])));

			foreach ($this->headers as $name => $value) {
				header($name . ': ' . trim($value));
			}

			header('content-length: ' . $this->getLength());

			if ($this->body !== null) {
				echo $this->body->getBytes();
			}

			exit();
		}

		/**
		 * This method sets the message's body.
		 *
		 * @access public
		 * @param mixed $body                                       the message's body
		 */
		public function setBody($body = null) {
			if (is_object($body) && ($body instanceof IO\File)) {
				$this->body = $body;
			}
			else if ($body !== null) {
				$this->body = new IO\StringBuffer($body);
			}
			else {
				$this->body = null;
			}
		}

		/**
		 * This method sets the header with specified name.
		 *
		 * @access public
		 * @param string $name                                      the name of the header
		 * @param string $value                                     the value of the header
		 */
		public function setHeader(string $name, ?string $value) {
			$name = strtolower($name);
			if (!in_array($name, array('content-length'))) {
				if ($value !== null) {
					$this->headers->putEntry($name, Core\Convert::toString($value));
				}
				else {
					$this->headers->removeKey($name);
				}
			}
		}

		/**
		 * This method sets the headers with the specified name/value pairs.
		 *
		 * @access public
		 * @param array $headers                                    the headers associated with the message
		 */
		public function setHeaders(array $headers) {
			foreach ($headers as $name => $value) {
				$this->setHeader($name, $value);
			}
		}

		/**
		 * This method sets the message's id.
		 *
		 * @access public
		 * @param string $id
		 * @throws Throwable\Parse\Exception                        the message id
		 */
		public function setMessageId(?string $id) {
			if ($this->id !== null) {
				$this->id = $id;
			}
			else {
				$this->id = $this->__hashCode();
			}
		}

		/**
		 * This method sets the HTTP protocol.
		 *
		 * @access public
		 * @param string $protocol                                  the HTTP protocol to be set
		 */
		public function setProtocol(string $protocol) {
			$this->protocol = strtoupper($protocol);
		}

		/**
		 * This method sets the HTTP status code.
		 *
		 * @access public
		 * @param integer $status                                   the HTTP status code to be set
		 * @throws Throwable\InvalidArgument\Exception              indicates the specified status
		 *                                                          code is not known
		 */
		public function setStatus(int $status) {
			if (!isset(static::$statuses[$status])) {
				throw new Throwable\InvalidArgument\Exception('Invalid status code. Expected an HTTP status code, but got ":status".', array(':status' => $status));
			}
			$this->status = (int) $status;
		}

		/**
		 * This method returns a singleton instance of the class.
		 *
		 * @access public
		 * @static
		 * @return Core\IMessage                                    a reference to the singleton instance
		 */
		public static function instance() : Core\IMessage {
			if (static::$instance === null) {
				static::$instance = new static();
			}
			return static::$instance;
		}

	}

}