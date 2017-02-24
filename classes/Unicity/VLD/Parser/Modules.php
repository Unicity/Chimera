<?php

return array(
	'eq' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	'gt' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
	'ge' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualTo',
	'lt' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThan',
	'le' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualTo',
	'ne' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualTo',

	'gt_length' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanLength',

	'is_null' => '\\Unicity\\VLD\\Parser\\Module\\IsNull',
	'is_undefined' => '\\Unicity\\VLD\\Parser\\Module\\IsUndefined',
	'is_unset' => '\\Unicity\\VLD\\Parser\\Module\\IsUnset',

	'is_required' => '\\Unicity\\VLD\\Parser\\Module\\IsRequired',

	'is_iban' => '\\Unicity\\VLD\\Parser\\Module\\IsIBAN',

	'matches_regex' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
	'matches_schema' => '\\Unicity\\VLD\\Parser\\Module\\MatchesSchema',
);
