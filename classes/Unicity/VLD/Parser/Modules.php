<?php

return array(
	'bic' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
		'policy' => '/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/'
	],
	'eq' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	],
	'eq_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualToLength',
	],
	'ge' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualTo',
	],
	'ge_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualToLength',
	],
	'gt' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
	],
	'gt_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanLength',
	],
	'iban' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsIBAN',
	],
	'in' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsEnum',
	],
	'le' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualTo',
	],
	'le_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualToLength',
	],
	'lt' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThan',
	],
	'lt_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanLength',
	],
	'ne' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualTo',
	],
	'ne_length' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualToLength',
	],
	'null' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsNull',
	],
	'regex' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
	],
	'required' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsRequired',
	],
	'schema' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\MatchesSchema',
	],
	'undefined' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsUndefined',
	],
	'unset' => [
		'class' => '\\Unicity\\VLD\\Parser\\Module\\IsUnset',
	],
);
