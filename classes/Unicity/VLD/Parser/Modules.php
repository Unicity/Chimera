<?php

return array(
	'schema' => '\\Unicity\\VLD\\Parser\\Module\\HasSchema',

	'eq' => '\\Unicity\\VLD\\Parser\\Module\\IsEqualTo',
	'gt' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThan',
	'ge' => '\\Unicity\\VLD\\Parser\\Module\\IsGreaterThanOrEqualTo',
	'lt' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThan',
	'le' => '\\Unicity\\VLD\\Parser\\Module\\IsLesserThanOrEqualTo',
	'ne' => '\\Unicity\\VLD\\Parser\\Module\\IsNotEqualTo',

	'null' => '\\Unicity\\VLD\\Parser\\Module\\IsNull',
	'undefined' => '\\Unicity\\VLD\\Parser\\Module\\IsUndefined',
	'unset' => '\\Unicity\\VLD\\Parser\\Module\\IsUnset',

	'req' => '\\Unicity\\VLD\\Parser\\Module\\IsRequired',

	'regex' => '\\Unicity\\VLD\\Parser\\Module\\MatchesRegex',
);
