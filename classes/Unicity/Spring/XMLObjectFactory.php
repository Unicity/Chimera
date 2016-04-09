<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011 Spadefoot Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Unicity\Spring {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Spring;
	use \Unicity\Throwable;

	/**
	 * This class handles object creation using an XML based container of object
	 * definitions.
	 *
	 * @access public
	 * @class
	 * @package Spring
	 *
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/xml/XmlBeanFactory.html
	 * @see http://static.springsource.org/spring/docs/2.5.x/reference/beans.html
	 * @see http://www.springframework.net/doc-latest/reference/html/objects.html
	 * @see http://msdn.microsoft.com/en-us/magazine/cc163739.aspx
	 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/xml/BeanDefinitionParserDelegate.html
	 */
	class XMLObjectFactory extends Core\Object implements Spring\IObjectFactory {

		/**
		 * This variable stores the XML resources.
		 *
		 * @access protected
		 * @var Common\Mutable\IList
		 */
		protected $resources;

		/**
		 * This variable stores a reference to the parser being used.
		 *
		 * @access protected
		 * @var Spring\Object\Parser
		 */
		protected $parser;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param \SimpleXMLElement $resource                       the resource to be used
		 */
		public function __construct(\SimpleXMLElement $resource = null) {
			$this->parser = new Spring\Object\Parser($this);
			$this->resources = new Common\Mutable\ArrayList();
			if ($resource !== null) {
				$this->addResource($resource);
			}
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->resources);
			//unset($this->parser);
		}

		/**
		 * This method adds a resource to this context.
		 *
		 * @access public
		 * @param \SimpleXMLElement $resource                       the resource to be added
		 */
		public function addResource(\SimpleXMLElement $resource) {
			$this->resources->addValue($this->import($resource, new Common\Mutable\HashSet()));
		}

		/**
		 * This method instantiates an object identified by the specified id.
		 *
		 * @access public
		 * @param string $id                                        the object's id
		 * @return object                                           an instance of the object
		 * @throws Throwable\Instantiation\Exception                indicates that a problem occurred
		 *                                                          during the instantiation
		 */
		public function getObject($id) {
			return $this->parser->getObjectFromIdRef($id, array());
		}

		/**
		 * This method returns the definition of an object matching the specified id.
		 *
		 * @access public
		 * @param string $id                                        the object's id
		 * @return Spring\Object\Definition                         the object's definition
		 */
		public function getObjectDefinition($id) {
			return $this->parser->getObjectDefinition($id);
		}

		/**
		 * This method returns an array of object ids that match the specified type (or if no type is specified
		 * then all ids in the current context).
		 *
		 * @access public
		 * @param string $type                                      the type of objects
		 * @return array                                            an array object ids
		 */
		public function getObjectIds($type = null) {
			return $this->parser->getObjectIds($type);
		}

		/**
		 * This method returns the scope of the object with the specified id.
		 *
		 * @access public
		 * @param string $id                                        the object's id
		 * @return string                             	            the scope of the the object with
		 *                                            	            the specified id
		 * @throws Throwable\Parse\Exception                        indicates that problem occurred while
		 *                                                          parsing
		 */
		public function getObjectScope($id) {
			return $this->parser->getObjectScope($id);
		}

		/**
		 * This method returns either the object's type for the specified id or null if the object's
		 * type cannot be determined.
		 *
		 * @access public
		 * @param string $id                                        the object's id
		 * @return string                                           the object's type
		 * @throws Throwable\InvalidArgument\Exception              indicates that an argument is of the
		 *                                                          incorrect type
		 */
		public function getObjectType($id) {
			return $this->parser->getObjectType($id);
		}

		/**
		 * This method returns a reference to the parser.
		 *
		 * @access public
		 * @return Spring\Object\Parser                             a reference to the parser
		 */
		public function getParser() {
			return $this->parser;
		}

		/**
		 * This method returns the resources in this context.
		 *
		 * @access public
		 * @return Common\Mutable\IList
		 */
		public function getResources() {
			return $this->resources;
		}

		/**
		 * This method determines whether an object with the specified id has been defined
		 * in the container.
		 *
		 * @access public
		 * @param string $id                                        the object's id
		 * @return boolean                                          whether an object with the specified id has
		 *                                                          been defined in the container
		 */
		public function hasObject($id) {
			return $this->parser->hasObject($id);
		}

		/**
		 * This method will include any additional Spring XML resources.
		 *
		 * @access protected
		 * @param \SimpleXMLElement $xml                            the XML resource to expand
		 * @param Common\Mutable\ISet $set                          a set of paths already loaded
		 * @return \SimpleXMLElement                                the expanded XML resource
		 * @throws Throwable\FileNotFound\Exception                 indicates that a file could not be
		 *                                                          located
		 */
		protected function import(\SimpleXMLElement $xml, Common\Mutable\ISet $set) {
			$xml->registerXPathNamespace('spring', Spring\Data\XML::NAMESPACE_URI);
			$elements = $xml->xpath('//spring:import');
			foreach ($elements as $element) {
				$attributes = $this->parser->getElementAttributes($element, null);
				if (isset($attributes['resource'])) {
					$resource = $this->parser->valueOf($attributes['resource']);
					if (!$set->hasValue($resource)) {
						$set->putValue($resource);

						$target = dom_import_simplexml($xml);
						$import = Spring\Data\XML::load(new IO\File($resource));
						$this->import($import, $set);

						$nodes = $this->parser->getElementChildren($import, null);
						foreach ($nodes as $node) {
							$source = dom_import_simplexml($node);
							$source = $target->ownerDocument->importNode($source, true);
							$target->appendChild($source);
						}
					}
				}
			}
			return $xml;
		}

	}

}