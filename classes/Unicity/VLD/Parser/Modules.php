<?php

return array(
	'eq' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	'ge' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualTo',
	'gt' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
	'le' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualTo',
	'lt' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThan',
	'ne' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualTo',

	'eq_length' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualToLength',
	'ge_length' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualToLength',
	'gt_length' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanLength',
	'le_length' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualToLength',
	'lt_length' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanLength',
	'ne_length' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualToLength',

	'in' => '\\Unicity\\VLD\\Parser\\Module\\IsEnum',

	'null' => '\\Unicity\\VLD\\Parser\\Module\\IsNull',
	'undefined' => '\\Unicity\\VLD\\Parser\\Module\\IsUndefined',
	'unset' => '\\Unicity\\VLD\\Parser\\Module\\IsUnset',

	'required' => '\\Unicity\\VLD\\Parser\\Module\\IsRequired',

	'schema' => '\\Unicity\\VLD\\Parser\\Module\\MatchesSchema',
	'regex' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
	
	'iban' => '\\Unicity\\VLD\\Parser\\Module\\IsIBAN',
);
