<?php

/**
 * Copyright 2015 Unicity International
 * Copyright 2011-2013 Spadefoot Team
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

namespace Unicity\Lexer\Scanner {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\Lexer;
	use \Unicity\Throwable;

	/**
	 * This class represents a tuple generated by a tokenizer.
	 *
	 * @access public
	 * @class
	 * @package Lexer
	 */
	class Tuple extends Core\Object {

		/**
		 * This variable stores the actual token.
		 *
		 * @access protected
		 * @var \Unicity\Common\String
		 */
		protected $token;

		/**
		 * This variable stores the type associated with the token.
		 *
		 * @access protected
		 * @var \Unicity\Lexer\Scanner\ITokenRule
		 */
		protected $type;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param \Unicity\Lexer\Scanner\TokenType $type            the type of token
		 * @param \Unicity\Common\String $token                     the actual token
		 */
		public function __construct(Lexer\Scanner\TokenType $type, Common\String $token) {
			$this->token = $token;
			$this->type = $type;
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->token);
			unset($this->type);
		}

		/**
		 * This method returns the value associated with the specified property.
		 *
		 * @access public
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property
		 * @throws \Unicity\Throwable\InvalidProperty\Exception     indicates that the specified property is
		 *                                                          either inaccessible or undefined
		 */
		public function __get($name) {
			if (!property_exists($this, $name)) {
				throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property :name is either inaccessible or undefined.', array(':name' => $name));
			}
			return $this->$name;
		}

	}

}