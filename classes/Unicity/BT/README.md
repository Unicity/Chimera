## Behavior Trees

### Quick Start Guide:

* http://web.cs.du.edu/~sturtevant/ai-s11/Lecture13.pdf
* http://www.moddb.com/groups/indievault/tutorials/game-ai-behavior-tree

### Tutorials:

* http://guineashots.com/2014/07/25/an-introduction-to-behavior-trees-part-1/
* http://guineashots.com/2014/08/10/an-introduction-to-behavior-trees-part-2/
* http://guineashots.com/2014/08/15/an-introduction-to-behavior-trees-part-3/
* http://www.gamasutra.com/blogs/ChrisSimpson/20140717/221339/Behavior_trees_for_AI_How_they_work.php
* http://chrishecker.com/My_Liner_Notes_for_Spore/Spore_Behavior_Tree_Docs
* http://www.gamasutra.com/view/feature/2250/gdc_2005_proceeding_handling_.php
* http://www.gamasutra.com/view/feature/130663/gdc_2005_proceeding_handling_.php?page=2
* https://github.com/libgdx/gdx-ai/wiki/Behavior-Trees

### Articles:

* http://aigamedev.com/open/article/bt-overview/
* http://aigamedev.com/open/article/fsm-reusable/
* http://aigamedev.com/open/article/hfsm-gist/
* http://aigamedev.com/open/article/sequence/
* http://aigamedev.com/open/article/selector/
* http://aigamedev.com/open/article/parallel/
* http://aigamedev.com/open/article/decorator/
* http://aigamedev.com/open/article/tasks/
* http://aigamedev.com/open/article/scheduler/
* http://aigamedev.com/open/article/scheduler-observer/
* http://aigamedev.com/open/article/approach-2/
* http://aigamedev.com/open/article/approach-3/

### Videos:

* http://aigamedev.com/open/articles/behavior-trees-part1/
* http://aigamedev.com/open/articles/behavior-trees-part2/
* http://aigamedev.com/open/articles/behavior-trees-part3/

### Implementation:

* http://guineashots.com/2014/09/24/implementing-a-behavior-tree-part-1/
* http://guineashots.com/2014/10/25/implementing-a-behavior-tree-part-2/
* http://blog.csdn.net/a3630623/article/details/41714327

### Examples:

* http://2dengine.com/doc/gs_ai.html
* http://2dengine.com/doc/tutorials/ai.lua
* http://2dengine.com/doc/tutorials/ai_example.lua
* https://code.google.com/p/owyl/source/browse/trunk/src/owyl/decorators.py?spec=svn4&r=4
* https://github.com/libgdx/gdx-ai

--------------------------------------------------------------------------------------------------------------------------

### Statuses

| Status       | Type   | Value                          | Description                                            |
| :----------: | :----: | :----------------------------: | :----------------------------------------------------: |
| `QUIT`       | enum   | -3                             | Indicates that the task just needs to quit.            |
| `FAILED`     | enum   | -2                             | Indicates that the task has failed to complete.        |
| `ERROR`      | enum   | -1                             | Indicates that the task encountered an error.          |
| `INACTIVE`   | enum   |  0                             | Indicates that the task does not need to be evaluated. |
| `ACTIVE`     | enum   |  1                             | Indicates that the task is NOT done processing.        |
| `SUCCESS`    | enum   |  2                             | Indicates that the task has successfully completed.    |

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:action /></i>

Description:

- This represents a `\Unicity\BT\Task\Action` object. An action performs logic to change the some state. It is a leaf node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.

Example:

````
<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:blackboard /></i>

Description:

- This represents a `\Unicity\Common\Mutable\IMap` object. A blackboard is a place to store information.  Typically, it is
  a shared resource between nodes, but can also be used to store information for later use by the same node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |

Settings:

- N/A.

Children:

- May contain 1 or more `<spring-bt:entry />` nodes.

Example:

````
<spring-bt:iterator>
	<spring-bt:blackboard>
		<spring-bt:entry key="key1" type="integer" value="123" />
	</spring-bt:blackboard>
</spring-bt:iterator>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:branch /></i>

Description:

- This represents a `\Unicity\BT\Task\Branch` object. A branch describes the relationships between nodes, and how and when
  nodes should be executed. It is a composite node. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:branch title="custom_branch" type="\Unicity\BT\Task\Ext\CustomBranch" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:breakpoint /></i>

Description:

- This represents a `\Unicity\BT\Task\Breakpoint` object. A breakpoint is typically used to quit processing the tree. It is
  a leaf node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.

Example:

````
<spring-bt:stub title="custom_breakpoint" type="\Unicity\BT\Task\Ext\CustomBreakpoint" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:composite /></i>

Description:

- This represents a `\Unicity\BT\Task\Composite` object. A composite describes the relationships between nodes, and how and
  when nodes should be executed. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:branch title="custom_branch" type="\Unicity\BT\Task\Ext\CustomBranch" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:condition /></i>

Description:

- This represents a `\Unicity\BT\Task\Condition` object. A condition acts a predicate to evaluate a particular state.  It
  is a leaf node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.

Example:

````
<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator /></i>

Description:

- This represents a `\Unicity\BT\Task\Decorator` object. A decorator provides a way to modify another node's execution or
  result. It is a composite node; however, it may only have one child node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Ext\CustomDecorator" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Counter" /></i>

Description:

- This represents a `\Unicity\BT\Task\Counter` object. A counter is a special decorator that causes its subtask to perform
  once for every X (i.e. so many) iterations.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type    | Values                        | Description                                           | Required   |
| :-------: | :-----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `max_count` | boolean | <b>10</b>                   | Indicates the interval between executions.            | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Counter" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Failer" /></i>

Description:

- This represents a `\Unicity\BT\Task\Failer` object. A failer is a special decorator that always returns a `failed` status.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type    | Values                        | Description                                           | Required   |
| :-------: | :-----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `quit`    | boolean | true, <b>false</b>            | Indicates that a `failed` status will be returned if set to `true`. | No |
| `error`   | boolean | true, <b>false</b>            | Indicates that a `failed` status will be returned if set to `true`. | No |
| `inactive` | boolean | true, <b>false</b>           | Indicates that a `failed` status will be returned if set to `true`. | No |
| `active`  | boolean | true, <b>false</b>            | Indicates that a `failed` status will be returned if set to `true`. | No |
| `success` | boolean | <b>true</b>, false            | Indicates that a `failed` status will be returned if set to `true`. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Failer" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Gambler" /></i>

Description:

- This represents a `\Unicity\BT\Task\Gambler` object. A gambler is a special decorator that causes its subtask to
  be processed when the specified probability has been satisfied.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type    | Values                        | Description                                           | Required   |
| :-------: | :-----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `callable`| string  | <b>rand</b>, mt_rand          | Indicates the name of the random function.            | No         |
| `odds`    | double  | <b>0.1</b>                    | Indicates the probability that the process will be executed. | No  |
| `options` | integer | <b>100</b>                    | Indicates the highest value to return.                | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Gambler" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Inverter" /></i>

Description:

- This represents a `\Unicity\BT\Task\Inverter` object. An inverter is a special decorator that causes a `success` status
  from its child node to return a `failed` status, and vice-versa.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Inverter" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Iterator" /></i>

Description:

- This represents a `\Unicity\BT\Task\Iterator` object. An iterator is a special decorator that acts similar to a traditional
  for loop that uses an indexer.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `reverse` | boolean | true, <b>false</b>           | Indicates the direction of the iterator.              | No         |
| `steps`   | integer | <b>1</b>                     | Defines the fully qualified class name of the object.  | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Iterator" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Limiter" /></i>

Description:

- This represents a `\Unicity\BT\Task\Limiter` object. A limiter is a special decorator that limits the number of times
  its child node can be called.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `limit`   | integer | <b>1</b>                     | Used to limit the number of times the wrapper can be processed. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Limiter" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Repeater" /></i>

Description:

- This represents a `\Unicity\BT\Task\Repeater` object. A repeater is a special decorator that will continue to call its
  child node until a certain condition is met.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `until`   | enum   | <b>2</b>, -2                  | Used to signal when repeater must stop: `2` for success and `-2` for failed. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Repeater" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:decorator type="\Unicity\BT\Task\Succeeder" /></i>

Description:

- This represents a `\Unicity\BT\Task\Succeeder` object. A succeeder is a special decorator that always returns a `success`
  status.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type    | Values                        | Description                                           | Required   |
| :-------: | :-----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `quit`    | boolean | true, <b>false</b>            | Indicates that a `success` status will be returned if set to `true`. | No |
| `failed`  | boolean | <b>true</b>, false            | Indicates that a `success` status will be returned if set to `true`. | No |
| `error`   | boolean | true, <b>false</b>            | Indicates that a `success` status will be returned if set to `true`. | No |
| `inactive` | boolean | true, <b>false</b>           | Indicates that a `success` status will be returned if set to `true`. | No |
| `active`  | boolean | true, <b>false</b>            | Indicates that a `success` status will be returned if set to `true`. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:decorator type="\Unicity\BT\Task\Succeeder" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:entry /></i>

Description:

- This represents the key/value pair for either a PHP associated array or an instance of `\Unicity\Common\Mutable\IMap`.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `key`     | string |                               | Used to assign a unique key to the associated value.  | Yes        |
| `type`    | enum   | boolean, integer, float, double, <b>string</b> | Defines the primitive type associated with the value. | No |
| `value`   | string |                               | Used to assign a value to the entry.                  | No         |
| `value-ref` | string |                             | Used to represent the id of an object as the value.   | No         |

Settings:

- N/A.

Children:

- May contain 1 `<spring:array />` node.
- May contain 1 `<spring:dictionary />` node.
- May contain 1 `<spring:expression />` node.
- May contain 1 `<spring:function />` node.
- May contain 1 `<spring:idref />` node.
- May contain 1 `<spring:list />` node.
- May contain 1 `<spring:map />` node.
- May contain 1 `<spring:null />` node.
- May contain 1 `<spring:object />` node.
- May contain 1 `<spring:ref />` node.
- May contain 1 `<spring:set />` node.
- May contain 1 `<spring:undefined />` node.
- May contain 1 `<spring:value />` node.
- May contain 1 `<spring-bt:action />` node.
- May contain 1 `<spring-bt:branch />` node.
- May contain 1 `<spring-bt:breakpoint />` node.
- May contain 1 `<spring-bt:composite />` node.
- May contain 1 `<spring-bt:condition />` node.
- May contain 1 `<spring-bt:decorator />` node.
- May contain 1 `<spring-bt:leaf />` node.
- May contain 1 `<spring-bt:logger />` node.
- May contain 1 `<spring-bt:parallel />` node.
- May contain 1 `<spring-bt:picker />` node.
- May contain 1 `<spring-bt:ref />` node.
- May contain 1 `<spring-bt:resetter />` node.
- May contain 1 `<spring-bt:selector />` node.
- May contain 1 `<spring-bt:semaphore />` node.
- May contain 1 `<spring-bt:sequence />` node.
- May contain 1 `<spring-bt:stub />` node.
- May contain 1 `<spring-bt:ticker />` node.
- May contain 1 `<spring-bt:timer />` node.

Example:

````
<spring-bt:blackboard>
	<spring-bt:entry key="key1" type="integer" value="123" />
	<spring-bt:entry key="key2">
		<spring:null />
	</spring-bt:entry>
	<spring-bt:entry key="key3" value-ref="" />
</spring-bt:blackboard>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:leaf /></i>

Description:

- This represents a `\Unicity\BT\Task\Leaf` object. A leaf node is a terminal node in the behavior tree; it does not have
  any children.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.

Example:

````
<spring-bt:leaf title="custom_leaf" type="\Unicity\BT\Task\Ext\CustomLeaf" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:logger /></i>

Description:

- This represents a `\Unicity\BT\Task\Logger` object. A logger is a special decorator that will cause a message to be written
  to a log regarding its child's current status.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:logger title="custom_logger" type="\Unicity\BT\Task\Ext\CustomLogger" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:parallel /></i>

Description:

- This represents a `\Unicity\BT\Task\Parallel` object. A parallel is a special branch that causes all of its child nodes
  to execute as long as constraints are not met. It is a composite node. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `shuffle` | boolean | true, <b>false</b>           | Determines whether the tasks are to be shuffle each time task is called. | No |
| `successes` | integer | <b>1</b>                   | Defines the number of tasks that must succeed.        | No         |
| `failures` | integer | <b>1</b>                    | Defines the number of tasks that can fail.            | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:parallel>
	<spring-bt:tasks>
		<spring-bt:selector title="custom_selector" type="\Unicity\BT\Task\Ext\CustomSelector" />
		<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
		<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
	</spring-bt:tasks>
</spring-bt:parallel>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:picker /></i>

Description:

- This represents a `\Unicity\BT\Task\Picker` object. A picker is a special selector that will pick only a specific
  index when executing. It is a composite node. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `index`   | integer | <b>0</b>                     | Indicates the index of the task to be select to be performed. | No |
| `shuffle` | boolean | true, <b>false</b>           | Determines whether the tasks are to be shuffle each time task is called. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:picker>
	<spring-bt:tasks>
		<spring-bt:sequence title="custom_sequence" type="\Unicity\BT\Task\Ext\CustomSequence" />
		<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
		<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
	</spring-bt:tasks>
</spring-bt:picker>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:ref /></i>

Description:

- This represents an id reference to a PHP class object.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `local`   | string |                               | Used to represent the id of an object in the same XML unit. | No   |
| `object`  | string |                               | Used to represent the id of an object in any XML unit. | No        |

Settings:

- N/A

Children:

- None.

Example:

````
<spring-bt:ref object="task-id" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:resetter /></i>

Description:

- This represents a `\Unicity\BT\Task\Resetter` object. A resetter is a special decorator that will cause its child to reset
  to either its initial state or a starting state.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

- None.

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:resetter title="custom_resetter" type="\Unicity\BT\Task\Ext\CustomResetter" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:selector /></i>

Description:

- This represents a `\Unicity\BT\Task\Selector` object. A selector is a special branch that acts similar to an if/else
  statement. It is a composite node. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `shuffle` | boolean | true, <b>false</b>           | Determines whether the tasks are to be shuffle each time task is called. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:selector>
	<spring-bt:tasks>
		<spring-bt:sequence title="custom_sequence" type="\Unicity\BT\Task\Ext\CustomSequence" />
		<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
		<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
	</spring-bt:tasks>
</spring-bt:selector>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:semaphore /></i>

Description:

- This represents a `\Unicity\BT\Task\Semaphore` object. A semaphore is a special decorator that can block another node from
  performing while a lock is acquired.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string | __CLASS__                     | The id associated with the lock.                      | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:semaphore title="custom_semaphore" type="\Unicity\BT\Task\Ext\CustomSemaphore" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:sequence /></i>

Description:

- This represents a `\Unicity\BT\Task\Sequence` object. A sequence is a special branch that process its child nodes in an
  orderly like fashion. It is a composite node. It is usually made up of one or more child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `shuffle` | boolean | true, <b>false</b>           | Determines whether the tasks are to be shuffle each time task is called. | No |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:sequence>
	<spring-bt:tasks>
		<spring-bt:selector title="custom_selector" type="\Unicity\BT\Task\Ext\CustomSelector" />
		<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
		<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
	</spring-bt:tasks>
</spring-bt:sequence>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:settings /></i>

Description:

- This represents a `\Unicity\Common\Mutable\IMap` object. These settings are used to configure a task upon initialization.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |

Settings:

- N/A.

Children:

- May contain 1 or more `<spring-bt:entry />` nodes.

Example:

````
<spring-bt:iterator>
	<spring-bt:settings>
		<spring-bt:entry key="steps" type="integer" value="10" />
	</spring-bt:settings>
</spring-bt:iterator>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:stub /></i>

Description:

- This represents a `\Unicity\BT\Task\Stub` object. A stub is typically used as a placeholder or for signally that something
  happended. It is a leaf node.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type    | Values                        | Description                                           | Required   |
| :-------: | :-----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `status`    | string | quit, failed, error, inactive, active, <b>success</b> | Indicates the return status. | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.

Example:

````
<spring-bt:stub title="custom_stub" type="\Unicity\BT\Task\Ext\CustomStub" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:tasks /></i>

Description:

- This represents a `\Unicity\Common\Mutable\IList` object.  It is used by a composite task to list any child nodes.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |

Settings:

- N/A.

Children:

- May contain 1 or more `<spring-bt:action />` nodes.
- May contain 1 or more `<spring-bt:branch />` nodes.
- May contain 1 or more `<spring-bt:breakpoint />` nodes.
- May contain 1 or more `<spring-bt:composite />` nodes.
- May contain 1 or more `<spring-bt:condition />` nodes.
- May contain 1 or more `<spring-bt:decorator />` nodes.
- May contain 1 or more `<spring-bt:leaf />` nodes.
- May contain 1 or more `<spring-bt:logger />` nodes.
- May contain 1 or more `<spring-bt:parallel />` nodes.
- May contain 1 or more `<spring-bt:picker />` nodes.
- May contain 1 or more `<spring-bt:ref />` nodes.
- May contain 1 or more `<spring-bt:resetter />` nodes.
- May contain 1 or more `<spring-bt:selector />` nodes.
- May contain 1 or more `<spring-bt:semaphore />` nodes.
- May contain 1 or more `<spring-bt:sequence />` nodes.
- May contain 1 or more `<spring-bt:stub />` nodes.
- May contain 1 or more `<spring-bt:ticker />` nodes.
- May contain 1 or more `<spring-bt:timer />` nodes.

Example:

````
<spring-bt:sequence>
	<spring-bt:tasks>
		<spring-bt:selector title="custom_selector" type="\Unicity\BT\Task\Ext\CustomSelector" />
		<spring-bt:condition title="custom_condition" type="\Unicity\BT\Task\Ext\CustomCondition" />
		<spring-bt:action title="custom_action" type="\Unicity\BT\Task\Ext\CustomAction" />
	</spring-bt:tasks>
</spring-bt:sequence>
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:ticker /></i>

Description:

- This represents a `\Unicity\BT\Task\Ticker` object. A ticker is a special decorator that restricts the frequency at which
  a particular node can be called.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `interval` | integer | <b>1000</b>                 | The timespan in milliseconds between ticks.           | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:ticker title="custom_ticker" type="\Unicity\BT\Task\Ext\CustomTicker" />
````

--------------------------------------------------------------------------------------------------------------------------

#### <i>\<spring-bt:timer /></i>

Description:

- This represents a `\Unicity\BT\Task\Timer` object. A timer is a special decorator that restricts when and for how long its
  child node can be called.

Attributes:

| Attribute | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `id`      | string |                               | Used to assign a unique selector to the node.         | No         |
| `name`    | string |                               | Used to assign a set of alias selector for the node.  | No         |
| `scope`   | enum   | <b>singleton</b>, prototype, session | Defines how the object is to be instantiated.  | No         |
| `title`   | string |                               | Used to assign a title to the node.                   | No         |
| `type`    | string |                               | Defines the fully qualified class name of the object. | No         |

Settings:

| Setting   | Type   | Values                        | Description                                           | Required   |
| :-------: | :----: | :---------------------------: | :---------------------------------------------------: | :--------: |
| `delay`   | integer | <b>0</b>                     | The timespan in milliseconds at which to start the task. | No      |
| `duration` | integer | <b>1000</b>                 | The timespan in milliseconds for how long to run.     | No         |

Children:

- May contain 1 `<spring:blackboard />` node.
- May contain 1 `<spring:settings />` node.
- May contain 1 `<spring:tasks />` node.

Example:

````
<spring-bt:timer title="custom_timer" type="\Unicity\BT\Task\Ext\CustomTimer" />
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

Settings:

- N/A.

Example:

````
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
````
