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

	use \Unicity\Core;

	/**
	 * This class encapsulates schema related information.
	 *
	 * @access public
	 * @abstract
	 * @class
	 */
	abstract class Schema extends Core\Object {

		/**
		 * This constant represents the default namespace used by Spring XML for behavior
		 * trees.
		 *
		 * @const string
		 */
		const NAMESPACE_URI = 'http://static.unicity.com/modules/xsd/spring-bt.xsd';

	}

}