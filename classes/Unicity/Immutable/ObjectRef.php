<?php

namespace Unicity\Immutable;

final class ObjectRef implements IObjectRef
{
    #region Properties

    private $idref;
    private $value;

    #endregion

    #region Instance Methods

    final private function __construct(string $idref, $value)
    {
        $this->idref = $idref;
        $this->value = $value;
    }

    final public function apply(callable $operator, $params = null): IObjectRef
    {
        return $operator($this, $params);
    }

    final public function count(): int
    {
        if (is_object($this->value)) {
            return count(get_object_vars($this->value));
        }
        if (is_array($this->value)) {
            return count($this->value);
        }

        return 0;
    }

    final public function current()
    {
        return $this->__get($this->key());
    }

    public function __destruct()
    {
        unset($this->idref);
        unset($this->value);
    }

    final public function dump(bool $exit = true): IObjectRef
    {
        var_dump($this->value);
        if ($exit) {
            exit();
        }

        return $this;
    }

    final public function __get($key)
    {
        if (is_object($this->value)) {
            return new ObjectRef(implode('.', [$this->idref, $key]), isset($this->value->{$key}) ? $this->value->{$key} : null);
        }
        if (is_array($this->value)) {
            return new ObjectRef(implode('.', [$this->idref, $key]), isset($this->value[$key]) ? $this->value[$key] : null);
        }

        return new ObjectRef(implode('.', [$this->idref, $key]), null);
    }

    final public function idref(): string
    {
        return $this->idref;
    }

    public function isArray(): bool
    {
        if (is_array($this->value)) {
            if (empty($this->value)) {
                return true;
            }
            $keys = array_keys($this->value);

            return (array_keys($keys) === $keys);
        }

        return false;
    }

    public function isBoolean(): bool
    {
        return is_bool($this->value);
    }

    public function isInteger(): bool
    {
        return (filter_var($this->value, FILTER_VALIDATE_INT) !== false);
    }

    public function isNull(): bool
    {
        return is_null($this->value);
    }

    public function isNumber(): bool
    {
        return is_numeric($this->value);
    }

    public function isObject(): bool
    {
        if (is_object($this->value)) {
            return true;
        }
        if (is_array($this->value) && !empty($this->value)) {
            $keys = array_keys($this->value);

            return (array_keys($keys) !== $keys);
        }

        return false;
    }

    final public function __isset($key): bool
    {
        if (is_object($this->value)) {
            return isset($this->value->{$key});
        }
        if (is_array($this->value)) {
            return isset($this->value[$key]);
        }

        return false;
    }

    public function isString(): bool
    {
        return is_string($this->value);
    }

    final public function jsonSerialize()
    {
        return $this->value;
    }

    final public function key()
    {
        if (is_object($this->value) || is_array($this->value)) {
            return key($this->value);
        }

        return null;
    }

    final public function merge(array $array): IObjectRef
    {
        return ObjectRef::box($this->idref(), ObjectRef::mergeArrays($this->value(), $array));
    }

    final public function next(): void
    {
        if (is_object($this->value) || is_array($this->value)) {
            next($this->value);
        }
    }

    final public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    final public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    final public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    final public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    final public function plugin(string $idref): IObjectRef
    {
        return new ObjectPluginRef(ObjectPluginRef::buildIdref($idref, $this), $this);
    }

    final public function preview(bool $exit = true): IObjectRef
    {
        echo (is_object($this->value) || is_array($this->value)) ? json_encode($this->value) : strval($this->value);
        if ($exit) {
            exit();
        }

        return $this;
    }

    final public function put(object $object): IObjectRef
    {
        return ObjectRef::box($this->idref(), ObjectRef::mergeObjects($this->value(), $object));
    }

    final public function rewind()
    {
        if (is_object($this->value) || is_array($this->value)) {
            reset($this->value);
        }
    }

    final public function __set($key, $value)
    {
        // do nothing
    }

    final public function __unset($key)
    {
        // do nothing
    }

    final public function use(string $idref, callable $operator): IObjectRef
    {
        return $operator($this->plugin($idref));
    }

    final public function valid(): bool
    {
        return ($this->key() !== null);
    }

    final public function value()
    {
        if (is_array($this->value) || is_object($this->value)) {
            return json_decode(json_encode($this->value));
        }

        return $this->value;
    }

    #endregion

    #region Initialization Methods

    final public static function box(string $idref, $value): IObjectRef
    {
        return new ObjectRef(ObjectRef::buildIdref($idref), $value);
    }

    final public static function make(string $idref, $value): IObjectRef
    {
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

    private static $schemas = [];

    final public static function bootstrap(string $file): void
    {
        self::$schemas = array_merge(self::$schemas, include($file));
    }

    final private static function getSchema(string $schema)
    {
        return ObjectRef::hasSchema($schema) ? ObjectRef::$schemas[$schema] : null;
    }

    final private static function hasSchema(string $schema): bool
    {
        return array_key_exists($schema, ObjectRef::$schemas);
    }

    #endregion

    #region Object Helpers

    final private static function buildIdref(string $idref): string
    {
        $idref = trim($idref, ". \t\n\r\0\x0B");
        if ($idref === '') {
            return '$';
        }
        if (preg_match('/^' . preg_quote('$.') . '/', $idref)) {
            return $idref;
        }

        return implode('.', ['$', $idref]);
    }

    final private static function mergeArrays(array $array0, array $array1): array
    {
        return array_merge($array0, $array1);
    }

    final private static function mergeObjects(object $object0, object $object1): object
    {
        return (object) array_merge((array) $object0, (array) $object1);
    }

    #endregion

}
