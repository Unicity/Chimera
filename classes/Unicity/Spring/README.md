### Inversion of Control (IoC) / Dependency Injection (DI) Tutorials

* http://martinfowler.com/articles/injection.html
* http://msdn.microsoft.com/en-us/magazine/cc163739.aspx

### SpringXML (in C#) Tutorial

* http://www.springframework.net/doc-latest/reference/html/objects.html
* http://www.springframework.net/doc-latest/reference/html/springobjectsxsd.html
* http://www.springframework.net/xsd/spring-objects.xsd

### SpringXML (in Java) Tutorial

* http://static.springsource.org/spring/docs/2.5.x/reference/beans.html
* http://www.springframework.org/schema/beans/spring-beans-3.0.xsd
* http://springindepth.com/book/in-depth-ioc-collections.html
* http://www.springframework.org/schema/util/spring-util.xsd

### SpringXML (in PHP) Tutorial

Below details each SpringXML tag that is currently supported by XMLObjectFactory and XMLObjectExporter.

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<array /></i>

Description:

- This represents a PHP indexed array.

Attributes:

- None.

Children:

- May contain 1 or more `<array />` nodes.
- May contain 1 or more `<dictionary />` nodes.
- May contain 1 or more `<expression />` nodes.
- May contain 1 or more `<function />` nodes.
- May contain 1 or more `<idref />` nodes.
- May contain 1 or more `<list />` nodes.
- May contain 1 or more `<map />` nodes.
- May contain 1 or more `<null />` nodes.
- May contain 1 or more `<object />` nodes.
- May contain 1 or more `<ref />` nodes.
- May contain 1 or more `<set />` nodes.
- May contain 1 or more `<undefined />` nodes.
- May contain 1 or more `<value />` nodes.

Example:

````
<property name="field1">
	<array>
		<ref object="oid-2" />
		<null />
		<value type="string">Test</value>
	</array>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<constructor-arg /></i>

Description:

- This represents the argument(s) of a PHP class object's constructor.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `expression` | string |                            | Used to represent an expression as the value.         | No         |
| `ref`     | string |                               | Used to represent the id of an object as the value.   | No         |
| `type`    | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value. | No |
| `value`   | string |                               | Used to assign a value to the argument.               | No         |

Children:

- May contain 1 `<array />` node.
- May contain 1 `<dictionary />` node.
- May contain 1 `<expression />` node.
- May contain 1 `<function />` node.
- May contain 1 `<idref />` node.
- May contain 1 `<list />` node.
- May contain 1 `<map />` node.
- May contain 1 `<null />` node.
- May contain 1 `<object />` node.
- May contain 1 `<ref />` node.
- May contain 1 `<set />` node.
- May contain 1 `<undefined />` nodes.
- May contain 1 `<value />` node.

Example:

````
<object id="oid-1" type="\Namespace\ClassName" />
	<constructor-arg type="integer" value="123" />
</objects>
<object id="oid-2" type="\Namespace\ClassName" />
	<constructor-arg>
		<value type="integer">123</value>
		<value type="string">Test</value>
	</constructor-arg>
</objects>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<dictionary /></i>

Description:

- This represents a PHP associated array.

Attributes:

- None.

Children:

- May contain 1 or more `<entry />` nodes.

Example:

````
<property name="field1">
	<dictionary>
		<entry key="key1" type="integer" value="123" />
		<entry key="key2">
			<null />
		</entry>
	</dictionary>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<entry /></i>

Description:

- This represents the key/value pair for either a PHP associated array or an instance of `\Unicity\Common\Mutable\IMap`.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `key`     | string |                               | Used to assign a unique key to the associated value.  | Yes        |
| `type`    | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value. | No |
| `value`   | string |                               | Used to assign a value to the entry.                  | No         |
| `value-ref` | string |                             | Used to represent the id of an object as the value.   | No         |

Children:

- May contain 1 `<array />` node.
- May contain 1 `<dictionary />` node.
- May contain 1 `<expression />` node.
- May contain 1 `<function />` node.
- May contain 1 `<idref />` node.
- May contain 1 `<list />` node.
- May contain 1 `<map />` node.
- May contain 1 `<null />` node.
- May contain 1 `<object />` node.
- May contain 1 `<ref />` node.
- May contain 1 `<set />` node.
- May contain 1 `<undefined />` nodes.
- May contain 1 `<value />` node.

Example:

````
<map>
	<entry key="key1" type="integer" value="123" />
	<entry key="key2">
		<null />
	</entry>
	<entry key="key3" value-ref="" />
</map>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<expression /></i>

Description:

- This represents a PHP expression.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `type`    | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value returned by the expression. | No |

Children:

- None.

Example:

````
<property name="field1">
	<expression type="integer">1 + 2</expression>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<function /></i>

Description:

- This represents a PHP callable type.

Attributes:

| Attribute  | Type   | Values                         | Description                                           | Required   |
| :--------: | :----: | :----------------------------: | :---------------------------------------------------: | :--------: |
| `delegate-method` | string |                         | Specifies the name of the method to be called.        | No         |
| `delegate-object` | string |                         | Specifies the name of the object where the method is defined. | No |

Children:

- May contain 1 `<array />` node.
- May contain 1 `<dictionary />` node.
- May contain 1 `<expression />` node.
- May contain 1 `<function />` node.
- May contain 1 `<idref />` node.
- May contain 1 `<list />` node.
- May contain 1 `<map />` node.
- May contain 1 `<null />` node.
- May contain 1 `<object />` node.
- May contain 1 `<ref />` node.
- May contain 1 `<set />` node.
- May contain 1 `<undefined />` nodes.
- May contain 1 `<value />` node.

Example:

````
<function delegate-object="\Namespace\ClassName" delegate-method="methodName" />
<function>
	<expression type="integer">1 + 2</expression>
</function>
<function>
	<value type="string">Test</value>
</function>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<idref /></i>

Description:

- This represents a PHP class object's id reference.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `local`   | string |                               | Used to represent the id of an object in the same XML unit. | No   |
| `object`  | string |                               | Used to represent the id of an object in any XML unit. | No        |

Children:

- None.

Example:

````
<property name="field1">
	<idref object="oid-2" />
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<list /></i>

Description:

- This represents a `\Unicity\Common\Mutable\IList` object.

Attributes:

- None.

Children:

- May contain 1 or more `<array />` nodes.
- May contain 1 or more `<dictionary />` nodes.
- May contain 1 or more `<expression />` nodes.
- May contain 1 or more `<function />` nodes.
- May contain 1 or more `<idref />` nodes.
- May contain 1 or more `<list />` nodes.
- May contain 1 or more `<map />` nodes.
- May contain 1 or more `<null />` nodes.
- May contain 1 or more `<object />` nodes.
- May contain 1 or more `<ref />` nodes.
- May contain 1 or more `<set />` nodes.
- May contain 1 or more `<undefined />` nodes.
- May contain 1 or more `<value />` nodes.

Example:

````
<property name="field1">
	<list>
		<ref object="oid-2" />
		<null />
		<value type="string">Test</value>
	</list>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<map /></i>

Description:

- This represents a `\Unicity\Common\Mutable\IMap` object.

Attributes:

- None.

Children:

- May contain 1 or more `<entry />` nodes.

Example:

````
<property name="field1">
	<map>
		<entry key="key1" type="integer" value="123" />
		<entry key="key2">
			<null />
		</entry>
	</map>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<null /></i>

Description:

- This represents a PHP `null` value.

Attributes:

- None.

Children:

- None.

Example:

````
<property name="field1">
	<null />
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<object /></i>

Description:

- This represents a PHP class object.  When an `<object />` node's parent node is the `<objects />` node, it is called an <b>outer object</b>; otherwise, it is called an <b>inner object</b>.

Attributes (for Outer):

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the object.       | Yes        |
| `factory-method` | string |                        | Specifies the name of the factory method to be called. | No        |
| `factory-object` | string |                        | Specifies the name of the object where the factory method is defined. | No        |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | Yes        |

Attributes (for Inner):

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `factory-method` | string |                        | Specifies the name of the factory method to be called. | No        |
| `factory-object` | string |                        | Specifies the name of the object where the factory method is defined. | No        |
| `type`    | string |                               | Defines the fully qualified class name of the object. | Yes        |

Children:

- May contain 1 or more `<property />` nodes.
- May contain 1 or more `<constructor-arg />` nodes.

Example:

````
<objects>
	<object id="oid-1" type="\Namespace\ClassName" scope="singleton" />
	<object id="oid-2" type="\Namespace\ClassName" scope="prototype" />
	<object id="oid-3" type="\Namespace\ClassName" scope="session" />
</objects>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<objects /></i>

Description:

- This is the root node.

Attributes:

| Attribute  | Type   | Values                         | Description                                           | Required   |
| :--------: | :----: | :----------------------------: | :---------------------------------------------------: | :--------: |
| `xmlns`    | string | http://static.unicity.com/modules/xsd/php/spring.xsd | Defines the default namespace for Spring XML. | Yes |
| `xmlns:xsi` | string | http://www.w3.org/2001/XMLSchema-instance | Defines the namespace for an XML schema instance. | Yes |
| `xmlns:spring` | string | http://static.unicity.com/modules/xsd/php/spring.xsd | Defines the namespace for Spring XML. | Yes  |
| `xsi:schemaLocation` | string | http://static.unicity.com/modules/xsd/php/spring.xsd http://static.unicity.com/modules/xsd/php/spring.xsd | Yes |

Children:

- May contain 1 or more `<object />` nodes.

Example:

````
<objects></objects>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<property /></i>

Description:

- This represents a PHP class object's property.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `expression` | string |                            | Used to represent an expression as the value.         | No         |
| `name`    | string |                               | Used to identify the name of the property.            | Yes        |
| `ref`     | string |                               | Used to represent the id of an object as the value.   | No         |
| `type`    | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value. | No |
| `value`   | string |                               | Used to assign a value to the property.               | No         |

Children:

- May contain 1 `<array />` node.
- May contain 1 `<dictionary />` node.
- May contain 1 `<expression />` node.
- May contain 1 `<function />` node.
- May contain 1 `<idref />` node.
- May contain 1 `<list />` node.
- May contain 1 `<map />` node.
- May contain 1 `<null />` node.
- May contain 1 `<object />` node.
- May contain 1 `<ref />` node.
- May contain 1 `<set />` node.
- May contain 1 `<undefined />` nodes.
- May contain 1 `<value />` node.

Example:

````
<object id="oid-1" type="\Namespace\ClassName" />
	<property name="field1" type="integer" value="123" />
	<property name="field2" type="double" expression="3.00 + 0.14" />
	<property name="field3" ref="oid-2" />
	<property name="field4"><null /></property>
</objects>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<ref /></i>

Description:

- This represents an id reference to a PHP class object.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `local`   | string |                               | Used to represent the id of an object in the same XML unit. | No   |
| `object`  | string |                               | Used to represent the id of an object in any XML unit. | No        |

Children:

- None.

Example:

````
<property name="field1">
	<ref object="oid-2" />
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<set /></i>

Description:

- This represents a `\Unicity\Common\Mutable\ISet` object.

Attributes:

- None.

Children:

- May contain 1 or more `<array />` nodes.
- May contain 1 or more `<dictionary />` nodes.
- May contain 1 or more `<expression />` nodes.
- May contain 1 or more `<function />` nodes.
- May contain 1 or more `<idref />` nodes.
- May contain 1 or more `<list />` nodes.
- May contain 1 or more `<map />` nodes.
- May contain 1 or more `<null />` nodes.
- May contain 1 or more `<object />` nodes.
- May contain 1 or more `<ref />` nodes.
- May contain 1 or more `<set />` nodes.
- May contain 1 or more `<undefined />` nodes.
- May contain 1 or more `<value />` nodes.

Example:

````
<property name="field1">
	<set>
		<ref object="oid-2" />
		<null />
		<value type="string">Test</value>
	</set>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<undefined /></i>

Description:

- This represents a `\Unicity\Core\Data\Undefined` object.

Attributes:

- None.

Children:

- None.

Example:

````
<property name="field1">
	<undefined />
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<value /></i>

Description:

- This represents a PHP typed value.

Attributes:

| Attribute   | Type   | Values                        | Description                                            | Required   |
| :---------: | :----: | :---------------------------: | :----------------------------------------------------: | :--------: |
| `type`      | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value. | No |
| `xml:space` | enum   | <b>preserve</b>               | Used to represent the id of an object in any XML unit. | No         |

Children:

- None.

Example:

````
<property name="field1">
	<value type="string">Test</value>
</property>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>XML Declaration</i>

Description:

- This indicates that the document contains XML. It must be declared on the first line in the XML document file.

Attributes:

| Attribute    | Type   | Values                         | Description                                           | Required   |
| :----------: | :----: | :----------------------------: | :---------------------------------------------------: | :--------: |
| `version`    | double | 1.0                            | Used to identify the version number.                  | Yes        |
| `encoding`   | enum   | UTF-8                          | Used to identify the character encoding.              | Yes        |
| `standalone` | enum   | yes, <b>no</b>                 | Used to indicate that the document stands alone.      | No         |

Example:

````
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
````
