<?php

namespace Unicity\Immutable {

	interface IObjectRef extends \ArrayAccess, \Countable, \Iterator, \JsonSerializable {

		public function apply(string $idref, callable $operator) : IObjectRef;
		public function dump(bool $exit = true) : IObjectRef;
		public function idref() : string;
		public function isArray() : bool;
		public function isBoolean() : bool;
		public function isInteger() : bool;
		public function isNull() : bool;
		public function isNumber() : bool;
		public function isObject() : bool;
		public function isString() : bool;
		public function plugin(string $idref) : IObjectRef;
		public function preview(bool $exit = true) : IObjectRef;
		public function value();

	}

}