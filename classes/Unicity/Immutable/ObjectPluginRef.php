<?php

namespace Unicity\Immutable {

	final class ObjectPluginRef implements IObjectRef {

		#region Properties

		private $idref;
		private $objectRef;

		#endregion

		#region Instance Methods

		public final function __construct(string $idref, ObjectRef $objectRef) {
			$this->idref = $idref;
			$this->objectRef = $objectRef;
		}

		public final function apply(callable $operator, $params = null): IObjectRef {
			return $this->objectRef->apply($operator, $params);
		}

		public final function __call(string $method, array $args) {
			array_unshift($args, $this->objectRef);
			$objectRef = call_user_func_array(
				[ObjectPluginRef::getPlugin($this->idref), $method],
				$args
			);
			return ($objectRef instanceof ObjectPluginRef) ? $objectRef : new ObjectPluginRef($this->idref, $objectRef);
		}

		public final function count() : int {
			return $this->objectRef->count();
		}

		public final function current() {
			return new ObjectPluginRef($this->idref, $this->objectRef->current());
		}

		public function __destruct() {
			unset($this->idref);
			unset($this->objectRef);
		}

		public final function dump(bool $exit = true) : IObjectRef {
			$this->objectRef->dump($exit);
			return $this;
		}

		public final function __get($key) {
			return new ObjectPluginRef($this->idref, $this->objectRef->__get($key));
		}

		public final function idref() : string {
			return $this->objectRef->idref();
		}

		public function isArray() : bool {
			return $this->objectRef->isArray();
		}

		public function isBoolean() : bool {
			return $this->objectRef->isBoolean();
		}

		public function isInteger() : bool {
			return $this->objectRef->isInteger();
		}

		public function isNull() : bool {
			return $this->objectRef->isNull();
		}

		public function isNumber() : bool {
			return $this->objectRef->isNumber();
		}

		public function isObject() : bool {
			return $this->objectRef->isObject();
		}

		public final function __isset($key) : bool {
			return $this->objectRef->__isset($key);
		}

		public function isString() : bool {
			return $this->objectRef->isString();
		}

		public final function jsonSerialize() {
			return $this->objectRef->jsonSerialize();
		}

		public final function key() {
			return $this->objectRef->key();
		}

		public final function merge(array $array) : IObjectRef {
			return $this->objectRef->merge($array);
		}

		public final function next() : void {
			$this->objectRef->next();
		}

		public final function offsetExists($offset) {
			return $this->objectRef->offsetExists($offset);
		}

		public final function offsetGet($offset) {
			return $this->__get($offset);
		}

		public final function offsetSet($offset, $value) {
			$this->objectRef->offsetSet($offset, $value);
		}

		public final function offsetUnset($offset) {
			$this->objectRef->offsetUnset($offset);
		}

		public final function plugin(string $idref) : IObjectRef {
			$idref = ObjectPluginRef::buildIdref($idref, $this->objectRef);
			if ($this->idref !== $idref) {
				return new ObjectPluginRef($idref, $this->objectRef);
			}
			return $this;
		}

		public final function preview(bool $exit = true) : IObjectRef {
			$this->objectRef->preview($exit);
			return $this;
		}

		public final function put(object $object) : IObjectRef {
			return $this->objectRef->put($object);
		}

		public final function rewind() {
			$this->objectRef->rewind();
		}

		public final function __set($key, $value) {
			$this->objectRef->__set($key, $value);
		}

		public final function __unset($key) {
			$this->objectRef->__unset($key);
		}

		public final function use(string $idref, callable $operator) : IObjectRef {
			return $operator($this->plugin($idref));
		}

		public final function valid() : bool {
			return $this->objectRef->valid();
		}

		public final function value() {
			return $this->objectRef->value();
		}

		#endregion

		#region Plugin Helpers

		private static $plugins = array();

		public final static function bootstrap(string $file) : void {
			ObjectPluginRef::$plugins = array_merge(ObjectPluginRef::$plugins, include($file));
		}

		public final static function buildIdref(string $idref, ObjectRef $objectRef) : string {
			$idref = trim($idref, ". \t\n\r\0\x0B");
			if ($idref === '') {
				return $objectRef->idref();
			}
			if (preg_match('/^' . preg_quote('@.') . '/', $idref)) {
				return $objectRef->idref() . substr($idref, 1);
			}
			if (preg_match('/^' . preg_quote('$.') . '/', $idref)) {
				return $idref;
			}
			return implode('.', [$objectRef->idref(), $idref]);
		}

		private final static function getPlugin(string $idref) {
			return ObjectPluginRef::hasPlugin($idref) ? ObjectPluginRef::$plugins[$idref] : null;
		}

		private final static function hasPlugin(string $idref) : bool {
			return isset(ObjectPluginRef::$plugins[$idref]);
		}

		public final static function import(string $idref, string $class) : void {
			ObjectPluginRef::$plugins[$idref] = $class;
		}

		#endregion

	}

}