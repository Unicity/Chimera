<?php

/**
 * Copyright 2015-2016 Unicity International
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types = 1);

namespace Unicity\VLD {

	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;
	use \Unicity\Throwable;
	use \Unicity\VLD;

	class Scanner extends Core\AbstractObject {

		public static function factory(IO\Reader $reader) : Lexer\Scanner {
			$scanner = new Lexer\Scanner($reader);

			$scanner->addRule(new Lexer\Scanner\TokenRule\Whitespace());
			$scanner->addRule(new Lexer\Scanner\TokenRule\BlockComment('/*', '*/'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\EOLComment('`'));

			$scanner->addRule(new VLD\Scanner\TokenRule\LeftArrow());

			$scanner->addRule(new Lexer\Scanner\TokenRule\Literal('"'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Number());

			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('('));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(')'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('['));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(']'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('{'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('}'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(','));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(':'));
			$scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('_'));

			$scanner->addRule(new Lexer\Scanner\TokenRule\Terminal('.'));

			$scanner->addRule(new VLD\Scanner\TokenRule\ArrayVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\BlockVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\BooleanVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\MapVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\MixedVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\NumberVariable());
			$scanner->addRule(new VLD\Scanner\TokenRule\StringVariable());

			$scanner->addRule(new Lexer\Scanner\TokenRule\Keyword([
				'dump', 'eval', 'halt', 'install', 'set', // simple statements
				'is', 'iterate', 'on', 'not', 'run', 'select', // complex statements
				'do', // symbols
				'false', 'true', // boolean values
				'null', // null value
			]));

			$scanner->addRule(new Lexer\Scanner\TokenRule\Unknown());

			$scanner->addIgnorable(Lexer\Scanner\TokenType::whitespace());

			//while ($scanner->next()) { var_dump($scanner->current()); } exit();

			return $scanner;
		}

	}

}