# Validation Service

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
<statement> = <eval> | <if> | <install> | <run> | <select> | <set>
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

## Statements

There are 6 types of statements: `eval`, `if`, `install`, `run`, `select`, and `set`.

### Eval Statements

An `eval` statement is used to perform some type of validation operation.  It does so by
executing the designated module.

```
eval("module", "path1").
eval("module", "path1", $policy).
eval("module", ["path1"]).
eval("module", ["path1"], $policy).
eval("module", ["path1", "path2"], $policy).
```

#### Parameters

1. Required: Defines the module to be executed.
2. Required: Defines the path to the component(s).
3. Optional: Defines the module's policy parameters.

### If Statements

An `if` statement is used to evaluate whether to execute a block of statements.  It does so
by executing the designated module.

```
if("module", "path1") { }.
if("module", "path1", $policy) { }.
if("module", ["path1"]) { }.
if("module", ["path1"], $policy) { }.
if("module", ["path1", "path2"], $policy) { }.
```

#### Parameters

1. Required: Defines the module to be executed.
2. Required: Defines the path to the component(s).
3. Optional: Defines the module's policy parameters.

### Install Statements

An `install` statement adds the module mappings into the current context.  These mappings
are stored in a PHP config file.

```
<?php
return array(
	'eq' => '\\Unicity\\VS\\Validation\\Module\\IsEqualTo',
	'gt' => '\\Unicity\\VS\\Validation\\Module\\IsGreaterThan',
);
```

This is loaded like so:

```
install("classpath:Unicity/VS/Validation/Modules.php").
```

#### Parameters

1. Required: Defines the location of the module mapping file.

### Run Statements

A `run` statement is used to control the way other statements are processed.  It does so
by executing the designated control.

```
run("control") { }.
run("control", $policy) { }.
```

#### Parameters

1. Required: Defines the control to be executed.
2. Optional: Defines the control's policy parameters.

#### Control Types

There are three types of controls: `all`, `sel`, and`seq`.

* `all` executes all statements and reports all violations (except when at least ## statements have reported no violations). It will always report all recommendations.
* `sel` executes all statements in order until one statement does not report any violations.  It will only report violations when no statements reports any.  It will always report all recommendations encountered.
* `seq` executes all statements in order until one reports a violation.  It will always report all recommendations encountered.

### Select Statements

A select statement perform a context switch.

```
select() { }.
select("path") {}.
```

#### Parameters

1. Optional: Defines the component's path that will become the new base entity.

### Set Statement

A `set` statement is used to define variables (i.e. it is an assignment statement).

```
set($variable, null).
set($variable, true).
set($variable, 123).
set($variable, 12.3).
set($variable, []).
set($variable, {}).
```
