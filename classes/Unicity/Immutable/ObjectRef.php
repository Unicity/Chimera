<?php

namespace Unicity\Immutable {

	final class ObjectRef implements IObjectRef {

		#region Properties

		private $idref;
		private $value;

		#endregion

		#region Instance Methods

		private final function __construct(string $idref, $value) {
			$this->idref = $idref;
			$this->value = $value;
		}

		public final function apply(string $idref, callable $operator) : IObjectRef {
			return $operator($this->plugin($idref));
		}

		public final function count() : int {
			if (is_object($this->value)) { return count(get_object_vars($this->value)); }
			if (is_array($this->value)) { return count($this->value); }
			return 0;
		}

		public final function current() {
			return $this->__get($this->key());
		}

		public function __destruct() {
			unset($this->idref);
			unset($this->value);
		}

		public final function dump(bool $exit = true) : IObjectRef {
			var_dump($this->value);
			if ($exit) {
				exit();
			}
			return $this;
		}

		public final function __get($key) {
			if (is_object($this->value)) {
				return new ObjectRef(implode('.', [$this->idref, $key]), isset($this->value->{$key}) ? $this->value->{$key} : null);
			}
			if (is_array($this->value)) {
				return new ObjectRef(implode('.', [$this->idref, $key]), isset($this->value[$key]) ? $this->value[$key] : null);
			}
			return new ObjectRef(implode('.', [$this->idref, $key]), null);
		}

		public final function idref() : string {
			return $this->idref;
		}

		public function isArray() : bool {
			return is_array($this->value);
		}

		public function isBoolean() : bool {
			return is_bool($this->value);
		}

		public function isInteger() : bool {
			return (filter_var($this->value, FILTER_VALIDATE_INT) !== false);
		}

		public function isNull() : bool {
			return is_null($this->value);
		}

		public function isNumber() : bool {
			return is_numeric($this->value);
		}

		public function isObject() : bool {
			return is_object($this->value);
		}

		public final function __isset($key) : bool {
			if (is_object($this->value)) { return isset($this->value->{$key}); }
			if (is_array($this->value)) { return isset($this->value[$key]); }
			return false;
		}

		public function isString() : bool {
			return is_string($this->value);
		}

		public final function jsonSerialize() {
			return $this->value;
		}

		public final function key() {
			if (is_object($this->value) || is_array($this->value)) {
				return key($this->value);
			}
			return null;
		}

		public final function next() : void {
			if (is_object($this->value) || is_array($this->value)) {
				next($this->value);
			}
		}

		public final function offsetExists($offset) : bool {
			return $this->__isset($offset);
		}

		public final function offsetGet($offset) {
			return $this->__get($offset);
		}

		public final function offsetSet($offset, $value) {
			$this->__set($offset, $value);
		}

		public final function offsetUnset($offset) {
			$this->__unset($offset);
		}

		public final function plugin(string $idref) : IObjectRef {
			return new ObjectPluginRef(ObjectPluginRef::buildIdref($idref, $this), $this);
		}

		public final function preview(bool $exit = true) : IObjectRef {
			echo (is_object($this->value) || is_array($this->value)) ? json_encode($this->value) : strval($this->value);
			if ($exit) {
				exit();
			}
			return $this;
		}

		public final function rewind() {
			if (is_object($this->value) || is_array($this->value)) {
				reset($this->value);
			}
		}

		public final function __set($key, $value) {
			// do nothing
		}

		public final function __unset($key) {
			// do nothing
		}

		public final function valid() : bool {
			return ($this->key() !== null);
		}

		public final function value() {
			if (is_array($this->value) || is_object($this->value)) {
				return json_decode(json_encode($this->value));
			}
			return $this->value;
		}

		#endregion

		#region Initialization Methods

		public final static function box(string $idref, $value) : IObjectRef {
			return new ObjectRef(ObjectRef::buildIdref($idref), $value);
		}

		public final static function make(string $idref, $value) : IObjectRef {
			$fields = ObjectRef::getSchema(ObjectRef::buildIdref($idref));
			if (is_array($fields)) {
				$buffer = new \stdClass();
				$value = json_decode(json_encode($value));
				foreach ($fields as $field) {
					if (isset($value->{$field}) && $value->{$field}) {
						$buffer->{$field} = $value->{$field};
					}
				}
				return new ObjectRef($idref, $buffer);
			}
			return new ObjectRef($idref, $value);
		}

		#endregion

		#region Schema Methods

		private static $schemas = array();

		public final static function bootstrap(string $file) : void {
			self::$schemas = array_merge(self::$schemas, include($file));
		}

		private final static function getSchema(string $schema) {
			return ObjectRef::hasSchema($schema) ? ObjectRef::$schemas[$schema] : null;
		}

		private final static function hasSchema(string $schema) : bool {
			return array_key_exists($schema, ObjectRef::$schemas);
		}

		#endregion

		#region Object Helpers

		private final static function buildIdref(string $idref) : string {
			$idref = trim($idref, ". \t\n\r\0\x0B");
			if ($idref === '') { return '$'; }
			if (preg_match('/^' . preg_quote('$.') . '/', $idref)) { return $idref; }
			return implode('.', ['$', $idref]);
		}

		#endregion

	}

}