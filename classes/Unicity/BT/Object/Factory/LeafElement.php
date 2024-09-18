<?php

declare(strict_types=1);

namespace Unicity\BT\Object\Factory;

use Unicity\BT;
use Unicity\Spring;
use Unicity\Throwable;

class LeafElement extends Spring\Object\Factory
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
        $attributes = $parser->getElementAttributes($element);

        if (!isset($attributes['type'])) {
            throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', [':tag' => $parser->getElementPrefixedName($element), ':attribute' => 'type']);
        }
        $type = $parser->valueOf($attributes['type']);

        $element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
        $children = $element->xpath('./spring-bt:policy');
        $policy = (!empty($children))
            ? $parser->getObjectFromElement($children[0])
            : null;

        $object = new $type($policy);

        if (!($object instanceof BT\Task\Leaf)) {
            throw new Throwable\Parse\Exception('Invalid type defined. Expected a task leaf, but got an element of type ":type" instead.', [':type' => $type]);
        }

        if (isset($attributes['title'])) {
            $object->setTitle($parser->valueOf($attributes['title']));
        }

        return $object;
    }

}
