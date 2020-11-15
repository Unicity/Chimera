<?php

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
	abstract class Schema extends Core\AbstractObject {

		/**
		 * This constant represents the default namespace used by Spring XML for behavior
		 * trees.
		 *
		 * @access public
		 * @const string
		 */
		public const NAMESPACE_URI = 'http://static.unicity.com/modules/xsd/php/spring-bt.xsd';

	}

}