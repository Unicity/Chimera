<?php

namespace Unicity\Immutable;

final class ObjectPluginRef implements IObjectRef
{
    #region Properties

    private $idref;
    private $objectRef;

    #endregion

    #region Instance Methods

    final public function __construct(string $idref, ObjectRef $objectRef)
    {
        $this->idref = $idref;
        $this->objectRef = $objectRef;
    }

    final public function apply(callable $operator, $params = null): IObjectRef
    {
        return $this->objectRef->apply($operator, $params);
    }

    final public function __call(string $method, array $args)
    {
        array_unshift($args, $this->objectRef);
        $objectRef = call_user_func_array(
            [ObjectPluginRef::getPlugin($this->idref), $method],
            $args
        );

        return ($objectRef instanceof ObjectPluginRef) ? $objectRef : new ObjectPluginRef($this->idref, $objectRef);
    }

    final public function count(): int
    {
        return $this->objectRef->count();
    }

    final public function current()
    {
        return new ObjectPluginRef($this->idref, $this->objectRef->current());
    }

    public function __destruct()
    {
        unset($this->idref);
        unset($this->objectRef);
    }

    final public function dump(bool $exit = true): IObjectRef
    {
        $this->objectRef->dump($exit);

        return $this;
    }

    final public function __get($key)
    {
        return new ObjectPluginRef($this->idref, $this->objectRef->__get($key));
    }

    final public function idref(): string
    {
        return $this->objectRef->idref();
    }

    public function isArray(): bool
    {
        return $this->objectRef->isArray();
    }

    public function isBoolean(): bool
    {
        return $this->objectRef->isBoolean();
    }

    public function isInteger(): bool
    {
        return $this->objectRef->isInteger();
    }

    public function isNull(): bool
    {
        return $this->objectRef->isNull();
    }

    public function isNumber(): bool
    {
        return $this->objectRef->isNumber();
    }

    public function isObject(): bool
    {
        return $this->objectRef->isObject();
    }

    final public function __isset($key): bool
    {
        return $this->objectRef->__isset($key);
    }

    public function isString(): bool
    {
        return $this->objectRef->isString();
    }

    final public function jsonSerialize()
    {
        return $this->objectRef->jsonSerialize();
    }

    final public function key()
    {
        return $this->objectRef->key();
    }

    final public function merge(array $array): IObjectRef
    {
        return $this->objectRef->merge($array);
    }

    final public function next(): void
    {
        $this->objectRef->next();
    }

    final public function offsetExists($offset)
    {
        return $this->objectRef->offsetExists($offset);
    }

    final public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    final public function offsetSet($offset, $value)
    {
        $this->objectRef->offsetSet($offset, $value);
    }

    final public function offsetUnset($offset)
    {
        $this->objectRef->offsetUnset($offset);
    }

    final public function plugin(string $idref): IObjectRef
    {
        $idref = ObjectPluginRef::buildIdref($idref, $this->objectRef);
        if ($this->idref !== $idref) {
            return new ObjectPluginRef($idref, $this->objectRef);
        }

        return $this;
    }

    final public function preview(bool $exit = true): IObjectRef
    {
        $this->objectRef->preview($exit);

        return $this;
    }

    final public function put(object $object): IObjectRef
    {
        return $this->objectRef->put($object);
    }

    final public function rewind()
    {
        $this->objectRef->rewind();
    }

    final public function __set($key, $value)
    {
        $this->objectRef->__set($key, $value);
    }

    final public function __unset($key)
    {
        $this->objectRef->__unset($key);
    }

    final public function use(string $idref, callable $operator): IObjectRef
    {
        return $operator($this->plugin($idref));
    }

    final public function valid(): bool
    {
        return $this->objectRef->valid();
    }

    final public function value()
    {
        return $this->objectRef->value();
    }

    #endregion

    #region Plugin Helpers

    private static $plugins = [];

    final public static function bootstrap(string $file): void
    {
        ObjectPluginRef::$plugins = array_merge(ObjectPluginRef::$plugins, include($file));
    }

    final public static function buildIdref(string $idref, ObjectRef $objectRef): string
    {
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

    final private static function getPlugin(string $idref)
    {
        return ObjectPluginRef::hasPlugin($idref) ? ObjectPluginRef::$plugins[$idref] : null;
    }

    final private static function hasPlugin(string $idref): bool
    {
        return isset(ObjectPluginRef::$plugins[$idref]);
    }

    final public static function import(string $idref, string $class): void
    {
        ObjectPluginRef::$plugins[$idref] = $class;
    }

    #endregion

}
