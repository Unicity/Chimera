<?php

/**
 * Copyright 2015 Unicity International
 * Copyright 2011 Spadefoot Team
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

namespace Unicity\Spring\Data\XML {

	use \Unicity\Core;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	/**
	 * This class provides a set of helper methods to process tokens in Spring XML.
	 *
	 * @access public
	 * @class
	 * @package Spring
	 */
	class Syntax extends Core\Object {

		/**
		 * This variable stores a list of valid primitive types.
		 *
		 * @access protected
		 * @static
		 * @var array
		 */
		protected static $primitives = array(
			'bool', 'boolean',
			'char',
			'date', 'datetime', 'time', 'timestamp',
			'decimal', 'double', 'float', 'money', 'number', 'real', 'single',
			'bit', 'byte', 'int', 'int8', 'int16', 'int32', 'int64', 'long', 'short', 'uint', 'uint8', 'uint16', 'uint32', 'uint64', 'integer', 'word',
			'ord', 'ordinal',
			'nil', 'null',
			'nvarchar', 'string', 'varchar', 'undefined'
		);

		/**
		 * This method evaluates whether the specified string matches the syntax for a class
		 * name.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a class name
		 */
		public static function isClassName($token) {
			return is_string($token) && preg_match('/^((\\\|_|\\.)?[a-z][a-z0-9]*)+$/i', $token);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for an id.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for an id
		 */
		public static function isId($token) {
			return is_string($token) && preg_match('/^[a-z0-9_]+$/i', $token);
		}

		/**
		 * This method evaluates whether the specified string is a valid idref.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @param \SimpleXMLElement $resource                       the resource to query
		 * @return boolean                                          whether the specified string is a valid
		 *                                                          idref
		 * @throws Throwable\InvalidArgument\Exception              indicates that an argument is incorrect
		 *
		 * @see http://stackoverflow.com/questions/1257867/regular-expressions-and-xpath-query
		 */
		public static function isIdref($token, \SimpleXMLElement $resource = null) {
			if ($resource !== null) {
				if (!static::isId($token)) {
					throw new Throwable\InvalidArgument\Exception('Invalid argument detected (id: :id).', array(':id' => $token));
				}
				$resource->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
				$nodes = $resource->xpath("/spring:objects/spring:object[@id='{$token}' or contains(@name,'{$token}')]");
				$nodes = array_filter($nodes, function(\SimpleXMLElement &$node) use ($token) {
					$attributes = $node->attributes();
					return ((isset($attributes['id']) && (Spring\Data\XML::valueOf($attributes['id']) == $token)) || (isset($attributes['name']) && in_array($token, preg_split('/(,|;|\s)+/', Spring\Data\XML::valueOf($attributes['name'])))));
				});
				return !empty($nodes);
			}
			return static::isId($token);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a key.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a key
		 */
		public static function isKey($token) {
			return is_string($token) && ($token != '');
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a method
		 * name.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a method name
		 */
		public static function isMethodName($token) {
			return is_string($token) && preg_match('/^[a-z_][a-z0-9_]*$/i', $token);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a primitive
		 * type.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a primitive type
		 */
		public static function isPrimitiveType($token) {
			return is_string($token) && in_array(strtolower($token), static::$primitives);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a property
		 * name.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a property name
		 */
		public static function isPropertyName($token) {
			return is_string($token) && preg_match('/^[a-z_][a-z0-9_]*$/i', $token);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a scope
		 * type.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a scope type
		 */
		public static function isScopeType($token) {
			// TODO uncomment session code
			// return is_string($token) && preg_match('/^(singleton|prototype|session)$/', $token);
			return is_string($token) && preg_match('/^(singleton|prototype)$/', $token);
		}

		/**
		 * This method evaluates whether the specified string matches the syntax for a space
		 * preserved attribute.
		 *
		 * @access public
		 * @static
		 * @param string $token                                     the string to be evaluated
		 * @return boolean                                          whether the specified string matches the syntax
		 *                                                          for a space preserved attribute
		 */
		public static function isSpacePreserved($token) {
			return is_string($token) && preg_match('/^preserve$/', $token);
		}

	}

}