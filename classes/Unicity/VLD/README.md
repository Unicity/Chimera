# Validation Service

## File

A VLD file is a simple text file and uses the file extension `.vld`.

## Syntax

The syntax of VLD in Backus-Naur Form:

```
<array> = <lbracket> <term>* <rbracket>
<boolean> = false | true
<block> = <lcurly> <statement>+ <rcurly>
<colon> = ":"
<comma> = ","
<eval> = eval <lparen> (<string> | <variable-string>) <comma> (<array> | <string> | <variable-string>) (<comma> <term>)? <rparen> <terminal>
<if> = if <lparen> (<string> | <variable>) <comma> (<array> | <string> | <variable>) (<comma> <term>)? <rparen> <block> <terminal>
<include> = include <lparen> (<string> | <variable-string>) <rparen> <terminal>
<install> = install <lparen> (<string> | <variable-string>) <rparen> <terminal>
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
<run> = run <lparen> (<string> | <variable-string>) (<comma> <term>)? <rparen> <block> <terminal>
<select> = select <lparen> (<string> | <variable-string>)? <rparen>  <terminal>
<set> = set <lparen> <variable> <comma> <term> <rparen>  <terminal>
<statement> = <eval> | <if> | <include> | <install> | <run> | <select> | <set>
<string> = '/^"[^"]*"$/'
<term> = <array> | <boolean> | <integer> | <map> | <null> | <real> | <string> | <variable>
<terminal> = "."
<variable> = <variable-boolean> | <variable-number> | <variable-string> | <variable-array> | <variable-map> | <variable-mixed>
<variable-boolean> = '/^\?[a-z0-9]$/i'
<variable-number> = '/^#[a-z0-9]$/i'
<variable-string> = '/^\$[a-z0-9]$/i'
<variable-array> = '/^@[a-z0-9]$/i'
<variable-map> = '/^%[a-z0-9]$/i'
<variable-mixed> = '/^\*[a-z0-9]$/i'
```

## Terms

All terms share the same syntax as JSON uses for its terms, with the exception of the variable
terms (which adapt their convention from a variety of other languages).

### Arrays

An array is defined using square brackets. It represents a collection of items.

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

A map (aka object) is defined using curly brackets. It represents a collection of name/value pairs.

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

#### Integers

An integer is a number without a decimal point.

```
123
```

#### Reals

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

A variable is used to store the value of another term.  There are 6 variable types
in the VLD language: boolean variables, number variables, string variables, array
variables, map variables, and mixed variables.  Each variable type has its own unique
prefix.

#### Boolean Variables

Boolean variables are prefixed using a question mark (which is an adaptation of the
syntax used in Ruby).

```
?variable
```

To declare a boolean variable, use the set statement.

```
set(?variable, true).
```

#### Number Variables

Number variables are prefixed using a hash/number symbol.

```
#variable
```

To declare a boolean variable, use the set statement.

```
set(#variable, 123).
set(#variable, 12.3).
```

#### String Variables

String variables are prefixed using a dollar sign (which is an adaptation of the syntax
used in Perl and PHP).

```
$variable
```

To declare a boolean variable, use the set statement.

```
set($variable, "some text").
```

#### Array Variables

Array variables are prefixed using an `@` sign (which is the same syntax used in Perl).

```
@variable
```

To declare an array variable, use the set statement.

```
set(@variable, []).
```

#### Map Variables

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
syntax in many languages).

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

## Statements

There are 7 types of statements: `eval`, `if`, `install`, `include`, `run`, `select`, and `set`.

### Simple Statements

Simple statements can be thought of as one-line statements (even though technically they can span
onto multiple lines).

#### Set Statement

A `set` statement is used to define variables and assign them values.

```
set(*variable, null).
set(?variable, true).
set(#variable, 123).
set(#variable, 12.3).
set(@variable, []).
set(%variable, {}).
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
eval("module", "path1", *policy).
eval("module", ["path1"]).
eval("module", ["path1"], *policy).
eval("module", ["path1", "path2"], *policy).
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
run("seq") { }.
run("seq", *policy) { }.
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
if("module", "path1", *policy) { }.
if("module", ["path1"]) { }.
if("module", ["path1"], *policy) { }.
if("module", ["path1", "path2"], *policy) { }.
```

##### Parameters

1. Required: Defines the module to be executed.
2. Required: Defines the path to the component(s).
3. Optional: Defines the module's policy parameters.
