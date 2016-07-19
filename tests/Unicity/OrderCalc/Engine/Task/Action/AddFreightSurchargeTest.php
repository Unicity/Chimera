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

	use \Unicity\UnitTest;

	/**
	 * @group OrderCalc
	 */
	class AddFreightSurchargeTest extends UnitTest\TestCase {

		/**
		 * This method provides the data for testing the "process" method.
		 *
		 * @return array
		 */
		public function data_process() {
			$data = array(
				array(array(1), array(1)),
			);
			return $data;
		}

		/**
		 * This method tests the "process" method.
		 *
		 * @dataProvider data_process
		 */
		public function test_process(array $provided, array $expected) {
			$this->assertSame($expected[0], $provided[0]);
		}

	}

}