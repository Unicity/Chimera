# Validation Service

## File

A VLD file is simple text file and uses the file extension `.vld`.

## Syntax

The syntax of VLD in Backus-Naur Form:

```
<array> = <lbracket> <term>* <rbracket>
<boolean> = false | true
<block> = <lcurly> <statement>+ <rcurly>
<colon> = ":"
<comma> = ","
<eval> = eval <lparen> (<string> | <variable>) <comma> (<array> | <string> | <variable>) (<comma> <term>)? <rparen> <terminal>
<if> = if <lparen> (<string> | <variable>) <comma> (<array> | <string> | <variable>) (<comma> <term>)? <rparen> <block> <terminal>
<include> = include <lparen> (<string> | <variable>) <rparen> <terminal>
<install> = install <lparen> (<string> | <variable>) <rparen> <terminal>
<integer> = '/^[+-]?(0|[1-9][0-9]*)$/'
<lbracket> = "["
<lcurly> = "{"
<lparen> = "("
<map> = <lcurly> (<string> <colon> <term> (<comma> <string> <colon> <term>)*)? <rcurly>
<null> = null
<real> = '/^[+-]?(0|[1-9][0-9]*)((\.[0-9]+)|([eE][+-]?(0|[1-9][0-9]*)))$/'
<rbracket> = "]"
<rcurly> = "}"
<rparen> = ")"
<run> = run <lparen> (<string> | <variable>) (<comma> <term>)? <rparen> <block> <terminal>
<select> = select <lparen> (<string> | <variable>)? <rparen>  <terminal>
<set> = set <lparen> <variable> <comma> <term> <rparen>  <terminal>
<statement> = <eval> | <if> | <include> | <install> | <run> | <select> | <set>
<string> = '/^"[^"]*"$/'
<term> = <array> | <boolean> | <integer> | <map> | <null> | <real> | <string> | <variable>
<terminal> = "."
<variable> = '/^\$[a-z0-9]$/i'
```

## Terms

### Arrays

An array is defined using square brackets (just like in JSON). It represents a collection of items.

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

A map (aka object) is defined using curly brackets (just like in JSON). It represents a collection
of name/value pairs.

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

There are two types of numbers: integer and real numbers.

An integer is a number without a decimal point.

```
123
```

A real is a number with a decimal point.

```
12.3
```

### Strings

A string is some test surrounded by double quotation marks.

```
"some text"
```

### Variables

A variable can be assigned the value of any other term.  A variable is defined like PHP variables with
a dollar sign for its prefix.

```
$example
```

## Comments

There are two types of comments: single-line comments and block comments.  Both types of comments are treated
by the parser as whitespace.

### Single-Line Comments

A single-line comment is started using the `#` symbol.  The comment will terminate at the end-of-the-line.

```
# some comment
```

### Block Comments

A block comment is started using `/*` and is terminated using `*/`.  It may be used to span a comment across
multiple lines.

```
/*
  some comment
*/
```

## Statements

There are 7 types of statements: `eval`, `if`, `install`, `include`, `run`, `select`, and `set`.

### Simple Statements

Simple statements can be thought of as one-line statements (even though technically they can span
onto multiple lines).

#### Set Statement

A `set` statement is used to define variables and assign them values.

```
set($variable, null).
set($variable, true).
set($variable, 123).
set($variable, 12.3).
set($variable, []).
set($variable, {}).
```

##### Parameters

1. Required: Defines the variable to be set.
2. Required: Defines the value to be assigned to the variable.

#### Install Statements

An `install` statement adds the module mappings into the current context.

```
install("classpath:Unicity/VLD/Parser/Modules.php").
```

##### Parameters

1. Required: Defines the location of the module mapping file.

##### Mapping File

These mappings are stored in a PHP config file.

```
<?php
return array(
	'eq' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	'gt' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
);
```

The keys can then be used with other statements, e.g. the `eval` and `if` statements.

#### Eval Statements

An `eval` statement is used to perform some type of validation operation.  It does so by
executing the designated module.

```
eval("module", "path1").
eval("module", "path1", $policy).
eval("module", ["path1"]).
eval("module", ["path1"], $policy).
eval("module", ["path1", "path2"], $policy).
```

##### Parameters

1. Required: Defines the module to be executed.
2. Required: Defines the path to the component(s).
3. Optional: Defines the module's policy parameters.

#### Include Statements

An `include` statement adds the statements in the designated `.vld` file to the current scope
of the program.

```
include("classpath:Unicity/MappingService/Impl/Hydra/API/Master/Validator/CreateCustomer.vld").
```

##### Parameters

1. Required: Defines the location of the `.vld` file to include.

### Complex Statements

A complex statement (or block statement) is a statement that encapsulates other statements
and is typically used to control the execution.

#### Run Statements

A `run` statement is used to control the way other statements are processed.  It does so
by executing the designated control.

```
run("control") { }.
run("control", $policy) { }.
```

##### Parameters

1. Required: Defines the control to be executed (e.g. `all`, `sel`, and `seq`).
2. Optional: Defines the control's policy parameters.

##### Control Types

There are three types of controls: `all`, `sel`, and `seq`.

* `all` executes all statements in order and will reports all violations, except when the number of successes meets the number required to not report any violations; however, it will always report all recommendations encountered.
* `sel` executes all statements in order until one statement does not report any violations.  It will only report violations when no statements reports any.  It will always report all recommendations encountered.
* `seq` executes all statements in order until one reports a violation.  It will always report all recommendations encountered.

#### Select Statements

A `select` statement perform a context switch.  By default, statements in its block are executed
according to the `seq` control flow.

```
select() { }.
select("path") {}.
```

##### Parameters

1. Optional: Defines the component's path that will become the new base entity.

#### If Statements

An `if` statement is used to evaluate whether to execute a block of statements.  It does so
by executing the designated module first before running its block.  By default, statements in
its block are executed according to the `seq` control flow.

```
if("module", "path1") { }.
if("module", "path1", $policy) { }.
if("module", ["path1"]) { }.
if("module", ["path1"], $policy) { }.
if("module", ["path1", "path2"], $policy) { }.
```

##### Parameters

1. Required: Defines the module to be executed.
2. Required: Defines the path to the component(s).
3. Optional: Defines the module's policy parameters.
