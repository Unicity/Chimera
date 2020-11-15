<?php

declare(strict_types = 1);

namespace Unicity\BT\Object\Factory {

	use \Unicity\Spring;
	use \Unicity\Throwable;

	class RefElement extends Spring\Object\Factory {

		/**
		 * This method returns an object matching the description specified by the element.
		 *
		 * @access public
		 * @param Spring\Object\Parser $parser                      a reference to the parser
		 * @param \SimpleXMLElement $element                        the element to be parsed
		 * @return mixed                                            an object matching the description
		 *                                                          specified by the element
		 * @throws Throwable\Parse\Exception                        indicates that a problem occurred
		 *                                                          when parsing
		 */
		public function getObject(Spring\Object\Parser $parser, \SimpleXMLElement $element) {
			$attributes = $parser->getElementAttributes($element);

			if (isset($attributes['local'])) {
				$object = $parser->getObjectFromIdRef($parser->valueOf($attributes['local']));
				return $object;
			}
			else if (isset($attributes['object'])) {
				$object = $parser->getObjectFromIdRef($parser->valueOf($attributes['object']));
				return $object;
			}
			else {
				throw new Throwable\Parse\Exception('Unable to process Spring XML. Tag ":tag" is missing ":attribute" attribute.', array(':tag' => 'ref', ':attribute' => 'object'));
			}
		}

	}

}