<?php

return array(
	'eq' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	'gt' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
	'ge' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualTo',
	'lt' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThan',
	'le' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualTo',
	'ne' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualTo',

	'gt_length' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanLength',

	'null' => '\\Unicity\\VLD\\Parser\\Module\\IsNull',
	'undefined' => '\\Unicity\\VLD\\Parser\\Module\\IsUndefined',
	'unset' => '\\Unicity\\VLD\\Parser\\Module\\IsUnset',

	'required' => '\\Unicity\\VLD\\Parser\\Module\\IsRequired',

	'iban' => '\\Unicity\\VLD\\Parser\\Module\\IsIBAN',

	'regex' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
	'schema' => '\\Unicity\\VLD\\Parser\\Module\\MatchesSchema',
);
