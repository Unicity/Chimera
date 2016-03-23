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

namespace Unicity\OrderCalc\Engine\Task\Action {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Config;
	use \Unicity\IO;
	use \Unicity\ORM;
	use \Unicity\Throwable;

	class Unmarshal extends BT\Task\Action {

		/**
		 * This method processes an entity.
		 *
		 * @access public
		 * @param integer $entityId                                 the entity id being processed
		 * @param BT\Application $application                       the application running
		 * @return integer                                          the status
		 */
		public function process(int $entityId, BT\Application $application) {
			$body = $entity->getBody();
			if ($body instanceof IO\File) {
				try {
					$reader = new Config\JSON\Reader($body, array_merge(
						$this->policy->toDictionary(),
						array('assoc' => false)
					));

					$map = new Common\Mutable\HashMap();
					$map->putEntry('Order', ORM\JSON\Model\Marshaller::unmarshal($reader, array(
						'case_sensitive' => true,
						'schema' => '\\Unicity\\MappingService\\Impl\\Hydra\\API\\Master\\Model\\Order',
					)));

					return BT\State\Success::with(new BT\Entity($map));
				}
				catch (Throwable\Runtime\Exception $ex) {
					return BT\State\Error::with(new BT\Entity($ex));
				}
			}
			return BT\Status::FAILED;
		}

	}

}
