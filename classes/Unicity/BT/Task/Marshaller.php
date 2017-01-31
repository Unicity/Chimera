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

namespace Unicity\BT\Task {

	use \Unicity\AOP;
	use \Unicity\BT;
	use \Unicity\Config;
	use \Unicity\Core;
	use \Unicity\Log;

	class Marshaller extends BT\Task\Responder {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param BT\Engine $engine                                 the engine running
		 * @param string $entityId                                  the entity id being processed
		 * @return integer                                          the status
		 */
		public function process(BT\Engine $engine, string $entityId) {
			$response = $engine->getResponse();

			$components = $engine->getEntity($entityId)->getComponents();
			$policy = Core\Convert::toDictionary($this->policy);

			$writer = new Config\JSON\Writer($components);
			$writer->config($policy);
			$writer->export($response);

			return BT\Status::QUIT;
		}

		/**
		 * This method runs when the concern's execution is successful (and a result is returned).
		 *
		 * @access public
		 * @param AOP\JoinPoint $joinPoint                          the join point being used
		 */
		public function afterReturning(AOP\JoinPoint $joinPoint) {
			$message = array(
				'class' => $joinPoint->getProperty('class'),
				'policy' => $this->policy,
				'status' => $joinPoint->getReturnedValue(),
				'task' => 'responder',
				'title' => $this->getTitle(),
			);

			Log\Logger::log(Log\Level::informational(), json_encode($message));
		}

	}

}
