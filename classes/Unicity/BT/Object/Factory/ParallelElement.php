<?php

declare(strict_types=1);

namespace Unicity\BT\Object\Factory;

use Unicity\BT;
use Unicity\Spring;
use Unicity\Throwable;

class ParallelElement extends Spring\Object\Factory
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

        $type = (isset($attributes['type']))
            ? $parser->valueOf($attributes['type'])
            : '\\Unicity\\BT\\Task\\Parallel';

        $element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
        $children = $element->xpath('./spring-bt:policy');
        $policy = (!empty($children))
            ? $parser->getObjectFromElement($children[0])
            : null;

        $object = new $type($policy);

        if (!($object instanceof BT\Task\Parallel)) {
            throw new Throwable\Parse\Exception('Invalid type defined. Expected a task parallel, but got an element of type ":type" instead.', [':type' => $type]);
        }

        if (isset($attributes['title'])) {
            $object->setTitle($parser->valueOf($attributes['title']));
        }

        $element->registerXPathNamespace('spring-bt', BT\Schema::NAMESPACE_URI);
        $children = $element->xpath('./spring-bt:tasks');
        if (!empty($children)) {
            foreach ($children as $child) {
                $object->addTasks($parser->getObjectFromElement($child));
            }
        }

        return $object;
    }

}
