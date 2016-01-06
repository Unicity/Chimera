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

namespace Unicity\SOAP {

	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;

	/**
	 * This class represents SOAP client to send requests.
	 *
	 * @access public
	 * @class
	 * @package SOAP
	 */
	class Client extends Core\Object {

		/**
		 * This variable stores the options associated with the request.
		 *
		 * @access protected
		 * @var array
		 */
		protected $options;

		/**
		 * This variable stores the URL to be used when making a request.
		 *
		 * @access protected
		 * @var \Unicity\IO\URL
		 */
		protected $url;

		/**
		 * This constructor initializes the class with the specified URL.
		 *
		 * @access public
		 * @param \Unicity\IO\URL $url                              the URL to be used
		 * @param array $options                                    the options to be associated
		 *                                                          with the request
		 */
		public function __construct(IO\URL $url, array $options = array()) {
			$this->url = $url;
			$this->options = $options;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->options);
			unset($this->url);
		}

		/**
		 * This method makes a SOAP request with the specified envelop.
		 *
		 * @access public
		 * @param string $envelop                                   the envelop to be sent
		 * @return mixed                                            the response message
		 * @throws \Unicity\Throwable\Runtime\Exception             indicates that the request could
		 *                                                          not be processed
		 *
		 * @see http://eureka.ykyuen.info/2011/05/05/php-send-a-soap-request-by-curl/
		 */
		public function send($envelop) {
			$request = \Leap\Core\Web\cURL\Request::factory($this->url)
				->setHeader('Content-Type', 'text/xml; charset="UTF-8"')
				->setHeader('Accept', 'text/xml')
				->setHeader('Cache-Control', 'no-cache')
				->setHeader('Pragma', 'no-cache')
				->setHeader('SOAPAction', '"run"')
				->setHeader('Content-Length', strlen($envelop));

			$defaults = array(
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 10,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			);

			$message = array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $envelop,
			);

			$options = $defaults + $this->options + $message;

			foreach ($options as $key => $value) {
				$request->setOption($key, $value);
			}

			$response = $request->post(TRUE);

			$status = $response->getHeader('http_code');

			if (!in_array($status, array(200))) {
				throw new Throwable\Runtime\Exception('Failed to execute SOAP request. Expected status code "200", but got ":status" instead.', array(':status' => $status));
			}

			return $response->getBody();
		}

	}

}