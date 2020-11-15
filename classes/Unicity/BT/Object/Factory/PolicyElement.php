<?php

declare(strict_types = 1);

namespace Unicity\BT\Object\Factory {

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	class PolicyElement extends Spring\Object\Factory {

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
			$object = new Common\Mutable\HashMap();

			$children = $parser->getElementChildren($element, BT\Schema::NAMESPACE_URI);
			if (!empty($children)) {
				foreach ($children as $child) {
					$name = $parser->getElementName($child);
					switch ($name) {
						case 'entry':
							$object->putEntries($parser->getObjectFromElement($child));
							break;
						default:
							throw new Throwable\Parse\Exception('Unable to process Spring XML. Expected an "entry" element, but got an element of type ":type" instead.', array(':type' => $parser->getElementPrefixedName($child)));
							break;
					}
				}
			}

			return $object;
		}

	}

}