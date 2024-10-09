<?php

declare(strict_types=1);

namespace Unicity\BT\Object\Factory;

use Unicity\Core;
use Unicity\Spring;
use Unicity\Throwable;

class EntryElement extends Spring\Object\Factory
{
    /**
     * This method returns an object matching the description specified by the element.
     *
     * @access public
     * @param Spring\Object\Parser $parser a reference to the parser
     * @param \SimpleXMLElement $element the element to be parsed
     * @return mixed an object matching the description
     *               specified by the element
     * @throws Throwable\Parse\Exception indicates that a problem occurred
     *                                   when parsing
     */
    public function getObject(Spring\Object\Parser $parser, \SimpleXMLElement $element)
    {
        $object = [];
        $attributes = $parser->getElementAttributes($element);

        if (!isset($attributes['key'])) {
            throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', [':tag' => $parser->getElementPrefixedName($element), ':attribute' => 'key']);
        }
        $key = $parser->valueOf($attributes['key']);
        if (!$parser->isKey($key)) {
            throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid entry key, but got ":key".', [':key' => $key]);
        }

        $children = $parser->getElementChildren($element, null);
        if (!empty($children)) {
            foreach ($children as $child) {
                $object[$key] = $parser->getObjectFromElement($child);
            }
        } elseif (isset($attributes['value-ref'])) {
            $object[$key] = $parser->getObjectFromIdRef($parser->valueOf($attributes['value-ref']));
        } elseif (isset($attributes['value'])) {
            $value = $parser->valueOf($attributes['value']);
            if (isset($attributes['value-type'])) {
                $type = $parser->valueOf($attributes['value-type']);
                if (!$parser->isPrimitiveType($type)) {
                    throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected a valid primitive type, but got ":type".', [':type' => $type]);
                }
                $value = Core\Convert::changeType($value, $type);
            }
            $object[$key] = $value;
        } else {
            throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', [':tag' => 'entry', ':attribute' => 'value']);
        }

        return $object;
    }

}
