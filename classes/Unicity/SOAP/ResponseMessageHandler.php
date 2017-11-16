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

namespace Unicity\SOAP {

	use \Unicity\Core;
	use \Unicity\EVT;
	use \Unicity\SOAP;

	abstract class ResponseMessageHandler extends Core\Object {

		/**
		 * This method processes the message and context.
		 *
		 * @access public
		 * @final
		 * @param SOAP\ResponseMessage $message                     the message to be processed
		 * @param EVT\Context $context                              the context to be processed
		 */
		public final function __invoke(SOAP\ResponseMessage $message, EVT\Context $context) {
			$exchange = new EVT\Exchange([
				'context' => $context,
				'message' => $message,
			]);

			$handler = new static();

			if ($handler->isSuccessful($exchange)) {
				$handler->onSuccess($exchange);
			}
			else {
				$handler->onFailure($exchange);
			}
		}

		/**
		 * This method tests whether the exchange was successful.
		 *
		 * @access protected
		 * @param EVT\Exchange $exchange                            the exchange to be evaluated
		 * @return bool                                             whether the exchange was successful
		 */
		protected function isSuccessful(EVT\Exchange $exchange) : bool {
			return true;
		}

		/**
		 * This method processes a failure message.
		 *
		 * @access public
		 * @param EVT\Exchange $exchange                            the exchange to be processed
		 */
		public function onFailure(EVT\Exchange $exchange) : void {
			// do nothing
		}

		/**
		 * This method processes a success message.
		 *
		 * @access public
		 * @param EVT\Exchange $exchange                            the exchange to be processed
		 */
		public function onSuccess(EVT\Exchange $exchange) : void {
			// do nothing
		}

	}

}