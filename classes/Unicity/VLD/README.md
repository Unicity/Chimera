# The VLD Programming Language.

## About

The VLD programming language is a simple validation scripting language that can be used to quickly write
validation scripts.  It is an interpretive language and has the power of calling PHP modules directly to
handle more complex validations when necessary.

VLD has adopted some of its core functionality from behavior tree design in game programming.  Many of the controls
in this language resemble a number of the common task nodes (e.g. the parallel node, the sequence node,
and the selector node) in behavior tree design.  Program feedback is managed by checking for the number of
violations encountered to determine whether a task (which are called statements in VLD) was successful or failed.
Generally, a statement is considered to have executed successfully if it returns 0 violations and statement is
considered to have failed when it returns 1 or more violations.

## File

A VLD file is a simple text file and uses the file extension `.vld`.

## Parsing

Files are currently executed via a class called `\Unicity\MappingService\Data\Validator`.  At the moment, we provide
input for processing via MappingService (but we could load the entity just as easily another way).

## Syntax

The syntax of VLD in Backus-Naur Form:

```
<array> = <lbracket> (<term> (<comma> <term>)*)? <rbracket>
<block> = <uri> | (<lcurly> <statement>+ <rcurly>) | <variable-block>
<boolean> = false | true
<colon> = ":"
<comma> = ","
<control> = <string> | <variable-string>
<dump> = dump <lparen> <rparen> (<larrow> <paths>)? <terminal>
<eval> = eval <lparen> <module> (<comma> <term>)? <rparen> <larrow> <paths> <terminal>
<halt> = halt <lparen> <rparen> <terminal>
<install> = install <lparen> <uri> <rparen> <terminal>
<integer> = '/^[+-]?(0|[1-9][0-9]*)$/'
<is> = is <lparen> <module> (<comma> <term>)? <rparen> <larrow> <paths> do <block> <terminal>
<iterate> = iterate <lparen> (<control> (<comma> <term>)?)? <rparen> do <block> <terminal>
<larrow> = "<-"
<lbracket> = "["
<lcurly> = "{"
<lparen> = "("
<map> = <lcurly> (<string> <colon> <term> (<comma> <string> <colon> <term>)*)? <rcurly>
<module> = <string> | <variable-string>
<not> = not <lparen> <module> (<comma> <term>)? <rparen> <larrow> <paths> do <block> <terminal>
<null> = null
<on> = on <lparen> <event> <rparen> do <block> <terminal>
<paths> = <array> | <variable-array>
<rbracket> = "]"
<rcurly> = "}"
<real> = '/^[+-]?(0|[1-9][0-9]*)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))$/'
<rparen> = ")"
<run> = run <lparen> (<control> (<comma> <term>)?)? <rparen> do <block> <terminal>
<select> = select <lparen> (<control> (<comma> <term>)?)? <rparen> (<larrow> <paths>)? do <block> <terminal>
<set> = set <lparen> (<variable-term> | <variable-block>) <comma> (<term> | <block>) <rparen> <terminal>
<statement> = <statement-simple> | <statement-complex>
<statement-simple> = <dump> | <eval> | <halt> | <install> | <set>
<statement-complex> = <is> | <not> | <on> | <run> | <select>
<string> = '/^"[^"]*"$/'
<term> = <array> | <boolean> | <integer> | <map> | <null> | <real> | <string> | <variable-term>
<terminal> = "."
<uri> = <string> | <variable-string>
<variable-array> = '/^@[a-z0-9]$/i'
<variable-block> = '/^\^[a-z0-9]$/i'
<variable-boolean> = '/^\?[a-z0-9]$/i'
<variable-map> = '/^%[a-z0-9]$/i'
<variable-mixed> = '/^\*[a-z0-9]$/i'
<variable-number> = '/^#[a-z0-9]$/i'
<variable-string> = '/^\$[a-z0-9]$/i'
<variable-term> = <variable-array> | <variable-boolean> | <variable-map> | <variable-mixed> | <variable-number> | <variable-string>
```

## Terms

All terms share the same syntax as JSON uses for its terms, with the exception of the variable
terms (which adapt their convention from a variety of other languages).

### Arrays

An array is defined using square brackets. It represents a collection of terms.

```
[
	"value1",
	"value2"
]
```

### Booleans

There are two boolean values: `true` and `false`.

```
true
false
```

### Maps

A map (aka hash or object) is defined using curly brackets. It represents a collection of name-value pairs.

```
{
	"name1": "value1",
	"name2": "value2",
}
```

### Nulls

A null value is a token placeholder for no value.

```
null
```

### Numbers

There are two types of numbers: integers and real numbers.

#### Integers

An integer is a number without a decimal point (for the sake of this document).

```
123
```

#### Reals

A real is a number with a decimal point (for the sake of this document).

```
12.3
```

### Strings

A string is some text surrounded by double quotation marks.

```
"some text"
```

## Blocks

Blocks are a special term that is used to encapsulate statements.  There are two types of block
terms: inline blocks and external blocks.


### Inline Blocks

An inline block is defined using curly braces.

```
{}
```

It may contain any number of other statements and may be stored in the block variable.

### External Blocks

An external block is defined using a string.  The string's text is file path to another
`.vld` file, which in turn be treated as the body of the block.

```
"classpath:file.vld"
```

It too may be stored in the block variable.

## Variables

A variable is used to store the value of another term.  There are 7 variable types
in the VLD language: array variables, block variables, boolean variables, map variables,
mixed variables, number variables, and string variables.  Each variable type has its own
unique prefix.

### Boolean Variables

Boolean variables are prefixed using a question mark (which is an adaptation of the
syntax used in Ruby).

```
?variable
```

To declare a boolean variable, use the set statement.

```
set(?variable, true).
```

### Number Variables

Number variables are prefixed using a hash/number symbol.

```
#variable
```

To declare a boolean variable, use the set statement.

```
set(#variable, 123).
set(#variable, 12.3).
```

### String Variables

String variables are prefixed using a dollar sign (which is an adaptation of the syntax
used in Perl and PHP).

```
$variable
```

To declare a boolean variable, use the set statement.

```
set($variable, "some text").
```

### Array Variables

Array variables are prefixed using an `@` sign (which is the same syntax used in Perl).

```
@variable
```

To declare an array variable, use the set statement.

```
set(@variable, []).
```

### Map Variables

Map variables are prefixed using a percent sign (which is the same syntax used in Perl).

```
%variable
```

To declare a map variable, use the set statement.

```
set(%variable, {}).
```

### Mixed Variables

Mixed variables are prefixed using an asterisk (which is an adaptation of the wildcard
syntax in many languages).  A mixed variable may store any other term's value, except for
a block term.

```
*variable
```

To declare a mixed variable, use the set statement.

```
set(*variable, null).
set(*variable, true).
set(*variable, 123).
set(*variable, 12.3).
set(*variable, []).
set(*variable, {}).
```

### Block Variables

Block variables are prefixed using an upcaret (which is an adaptation of the lambda syntax
used in Objective-C).

```
^variable
```

To declare a block variable, use the set statement.

```
set(^variable, {}).
set(^variable, "classpath:file.vld").
```

### Default Values

In event that a variable is not initialized, the interpreter will return a default value for that
variable when requested at runtime.

```
| Type    | Variable  | Default |
| ------- | --------- | ------- |
| array   | @variable | []      |
| block   | ^variable | {}      |
| boolean | ?variable | false   |
| map     | %variable | {}      |
| mixed   | *variable | null    |
| number  | #variable | 0       |
| string  | $variable | ''      |
```

Warning: Just note that once a variable is set in that scope or in a parent scope, that variable will no
longer return that default value.

## Comments

There are two types of comments: single-line comments and block comments.  Both types of comments are treated
by the parser as whitespace.

### Single-Line Comments

A single-line comment is started using the backtick symbol.  The comment will terminate at the end of the
line.

```
` some comment
```

### Block Comments

A block comment is started using `/*` and is terminated using `*/`.  It may be used to span a comment across
multiple lines.

```
/*
  some comment
*/
```

## Paths

A path is used to identify the key/index of a particular component in the entity.  Paths are, generally,
written as strings encapsulated in an array.

```
["path"]
["path1", "path2"]
```

### Dot Syntax

At the momement, scripts uses dot syntax to represent a component's path.

```
order.customer.id
order.lines.items.0.quantity
```

### JSONPath

[JSONPath](http://goessner.net/articles/JsonPath/) is used in the feedback report.

```
$.order.customer.id
$.order.lines.items[0].quantity
```

### Wildcards

In order to minimize verbose scripts, VLD offers the use of a wildcard symbol to represent the current
path/context.  A wildcard symbol is denoted by an underscore.

```
_
```

It can be used instead of an array to represent the path.

```
select() <- _ do {}.
```

An alternative to using the wildcard is to leave out the `<- _` segment completely.

```
select() do {}.
```

## Statements

There are 10 types of statements: `dump`, `eval`, `halt`, `install`, `is`, `iterate`, `on`, `not`, `run`,
`select`, and `set`.  They are separated into two groups: simple statements and complex statements.

### Simple Statements

Simple statements can be thought of as one-line statements (even though technically they can span
onto multiple lines).

#### Halt Statement

A `halt` statement is used to terminate the program.

```
halt().
```

#### Set Statement

A `set` statement is used to define variables and assign them values.

```
set(*variable, null).
set(?variable, true).
set(#variable, 123).
set(#variable, 12.3).
set(@variable, []).
set(%variable, {}).
set(^variable, { set(*variable, null). }).
```

##### Parameters

1. Required (Variable): Defines the variable to be set.
2. Required (Term of the Same Type): Defines the value to be assigned to the variable.

#### Dump Statement

A `dump` statement is used to print out component details.

```
dump().
dump() <- ["path"].
dump() <- ["path1", "path2"].
```

#### Install Statements

An `install` statement adds the module mappings into the current context.

```
install("classpath:Unicity/VLD/Parser/Modules.php").
```

##### Parameters

1. Required (String): Defines the location of the module mapping file.

##### Mapping File

These mappings are stored in a PHP config file.

```
<?php
return array(
	'eq' => [
		'class' => '\\Unicity\\VLD\\Module\\IsEqualTo',
	],
	'bic' => [
		'class' => '\\Unicity\\VLD\\Module\\MatchesRegex',
		'policy' => '/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/'
	],
);
```

The keys can then be used with other statements, e.g. the `eval`, `is`, and `not` statements. Note that
the policy can also be set in the mapping file, which makes it easy to define new modules without having
to write a new module class or to define it in your `.vld` file.

#### Eval Statements

An `eval` statement is used to perform the validation operation.  It does so by executing the designated
module.

```
eval("module") <- ["path"].
eval("module", *policy) <- ["path"].
eval("module", *policy) <- ["path1", "path2"].
```

##### Parameters

1. Required (String): Defines the module to be executed.
2. Optional (Any Term): Defines the module's policy parameters.

### Complex Statements

A complex statement (or block statement) is a statement that encapsulates 0 or more
statements and is typically used to control the flow of execution.

#### Run Statements

A `run` statement is used to control the way statements are processed within its block.  It
does so by executing the designated control.

```
run("seq") do {}.
run("seq", *policy) do {}.
```

##### Parameters

1. Optional (String): Defines the control to be executed (e.g. `all`, `sel`, and `seq`).
2. Optional (Any Term): Defines the control's policy parameters.

##### Control Types

There are three types of controls: `all`, `sel`, and `seq`.

* `all` executes all statements in order and will reports all violations, except when the number of successes meets the number required to not report any violations; however, it will always report all recommendations encountered.
* `sel` executes all statements in order until one statement does not report any violations.  It will only report violations when no statements reports any violation.  It will always report all recommendations encountered.
* `seq` executes all statements in order until one statement reports a violation.  It will always report all recommendations encountered.

##### Shortcuts

Each of the three `run` statement control types may be expressed using a shortcut inside a block term.

```
| Long Form         | Short Form |
| ----------------- | ---------- |
| run("all") do {}. | []         |
| run("sel") do {}. | ()         |
| run("seq") do {}. | {}         |
```

#### Select Statements

A `select` statement perform a context switch.  A `select` statement applys its block to each specified path.  The
designated control is done on the block level, not on the statement level (like the `run` statement).  By default,
statements in its block are executed according to the `seq` control flow.

```
select() do {}.
select() <- ["path"] do {}.
select() <- ["path1", "path2"] do {}.
select("seq") <- ["path1", "path2"] do {}.
select("seq", *policy) <- ["path1", "path2"] do {}.
```

##### Parameters

1. Optional (String): Defines the control to be executed (e.g. `all`, `sel`, and `seq`).
2. Optional (Any Term): Defines the control's policy parameters.

##### Control Types

There are three types of controls: `all`, `sel`, and `seq`.

* `all` executes all blocks in order and will reports all violations, except when the number of successes meets the number required to not report any violations; however, it will always report all recommendations encountered.
* `sel` executes all blocks in order until one block does not report any violations.  It will only report violations when no blocks reports any violations.  It will always report all recommendations encountered.
* `seq` executes all blocks in order until one block reports a violation.  It will always report all recommendations encountered.

#### Iterate Statement

An `iterate` statement is a similar to a `select` statement, except that it does a context switch on each item in the
current entity.  The designated control is done on the block level, not on the statement level (like the `run` statement).
By default, statements in its block are executed according to the `seq` control flow.

```
iterate() do {}.
iterate("seq") do {}.
iterate("seq", *policy) do {}.
```

##### Parameters

1. Optional (String): Defines the control to be executed (e.g. `all`, `sel`, and `seq`).
2. Optional (Any Term): Defines the control's policy parameters.

##### Control Types

It uses the same control types as a `select` statement.

#### Is Statements

An `is` statement is used to evaluate whether to execute a block of statements.  It does so
by executing the designated module first before running its block.  By default, statements in
its block are executed according to the `seq` control flow.

```
is("module") <- ["path"] do {}.
is("module", *policy) <- ["path"] do {}.
is("module", *policy) <- ["path1", "path2"] do {}.
```

##### Parameters

1. Required (String): Defines the module to be executed.
2. Optional (Any Term): Defines the module's policy parameters.

#### Not Statements

A `not` statement is the inverse of an `is` statement.  It does so by executing the designated
module first before running its block.  By default, statements in its block are executed
according to the `seq` control flow.

```
not("module") <- ["path"] do {}.
not("module", *policy) <- ["path"] do {}.
not("module", *policy) <- ["path1", "path2"] do {}.
```

##### Parameters

1. Required (String): Defines the module to be executed.
2. Optional (Any Term): Defines the module's policy parameters.

#### On Statements

An `on` statement is used to trigger its block when a particular event occurs.  By default, statements in
its block are executed according to the `seq` control flow.

```
on("event") do {}.
```

##### Parameters

1. Required (String): Defines the event to be listen for.

##### Event Types

An event can be any string of text.  This string will then be used to match against the value passed
in the HTTP header `X-Event-Type`.

```
X-Event-Type: submit
```

To allow for multiple events to be passed via this HTTP header name, it is possible to send a semicolon-separated
list of event types. 

```
X-Event-Type: click; change; submit
```

## Feedback

Results are stored in a feedback buffer.  A new feedback buffer is created every time a statement
is called.  Results are then merged up the tree based on the rules defined by the current running
control.  There are three types of controls: `all`, `sel`, `seq`.

There are two types of feedback stored in a buffer: recommendations and violations.  Recommendations
are actions that are said to be not critical enough to cause a failure.  These usually can be fixed
internally by, for example, an API rather than be pushed back to the end-user.  Violations, on the
other hand, are said to be critical enough to reject further processing.  Generally, all recommendations
will be reported.  However, violations will be reported in accordance with the rules defined by the
controls in the program.

### Data Structure

Feedback is stored in an map. For example:

```
{
  "recommendations": [
    {
      "fields": [
        {
          "field": "$.customer.id"
          "to": "1234567890"
        }
      ],
      "message": "Field value should be typed as "string".",
      "type": "Set"
    },
	{
	  "fields": [
		{
		  "field": "$.market"
		  "to": "US"
		}
	  ],
	  "message": "Field value must be equal to "US.",
	  "type": "Set"
	}
  ],
  "violations": [
    {
      "fields": [
        {
          "field": "$.customer.draftBankAccount.accountHolder"
        }
      ],
      "message": "Field length must be lesser than or equal to \"22\".",
      "type": "Mismatch"
    },
    {
      "fields": [
        {
          "field": "$.customer.draftBankAccount.iban"
        }
      ],
      "message": "Field value must match pattern.",
      "type": "Mismatch"
    }
  ]
}
```

The fields are represented using [JSONPath](http://goessner.net/articles/JsonPath/).

### Message Localization

The messages reported back to the end-user can be localized using a simple Java properties file.
To create a localized properties file duplicate the `Messages.properties` file and change its name
to your specific local.  For example:

```
Messages_ko_KR.properties
```

The first two letters are the language and the next two letters are the country.

To trigger this file to be called, simple set the HTTP header `HTTP_ACCEPT_LANGUAGE` to your specific
local.
