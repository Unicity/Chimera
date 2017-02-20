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

namespace Unicity\VS {

	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;
	use \Unicity\Throwable;
	use \Unicity\VS;

	class Parser extends Core\Object {

		/**
		 * This variable stores a reference to the IO reader.
		 *
		 * @access protected
		 * @var Lexer\Scanner
		 */
		protected $scanner;

		/**
		 * This constructor initializes the class.
		 *
		 * @access public
		 * @param IO\Reader $reader                                 a reference to the IO reader
		 */
		public function __construct(IO\Reader $reader) {
			$this->scanner = new Lexer\Scanner($reader);

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Whitespace());
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\EOLComment());

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Literal('"'));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Number());

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('('));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(')'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('['));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(']'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('{'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol('}'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(','));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Symbol(':'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Terminal('.'));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Variable());
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Keyword([
				'def', 'eval', 'run', // instructions
				'false', 'true', // booleans
				'null',
			]));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Unknown());

			$this->scanner->addIgnorable(Lexer\Scanner\TokenType::whitespace());
		}

		public function start() : void {
			$this->scanner->next();
			while (true) {
				$tuple = $this->scanner->current();
				if (is_null($tuple)) {
					break;
				}
				$this->Statement()->accept0();
			}
		}

		protected function ArrayTerm() : VS\Parser\ArrayTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsArrayTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$terms = array();
			$this->Symbol('[');
			if (!$this->IsSymbol($tuple, ']')) {
				$terms[] = $this->Term();
			}
			while (true) {
				if ($this->IsSymbol($tuple, ']')) {
					$this->Symbol(']');
					break;
				}
				$this->Symbol(',');
				$terms[] = $this->Term();
			}
			return new VS\Parser\ArrayTerm($terms);
		}

		protected function BooleanTerm() : VS\Parser\BooleanTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsBooleanTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\BooleanTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function DefStatement() : VS\Parser\DefStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol('(');
			$args[] = $this->VariableKey();
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(')');
			$this->Terminal();
			return new VS\Parser\DefStatement($args);
		}

		protected function EvalStatement() : VS\Parser\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol('(');
			$args[] = $this->TermOption('StringTerm', 'VariableTerm');
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(')');
			$this->Terminal();
			return new VS\Parser\EvalStatement($args);
		}

		protected function IntegerTerm() : VS\Parser\IntegerTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsIntegerTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\IntegerTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function IsArrayTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->IsSymbol($tuple, '[');
		}

		protected function IsBooleanTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && in_array((string) $tuple->token, ['false', 'true']));
		}

		protected function IsIntegerTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:INTEGER'));
		}

		protected function IsMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->IsSymbol($tuple, '{');
		}

		protected function IsNullTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'null'));
		}

		protected function IsRealTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:REAL'));
		}

		protected function IsStatement(Lexer\Scanner\Tuple $tuple, string $identifier) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === $identifier));
		}

		protected function IsStringTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'LITERAL'));
		}

		protected function IsSymbol(Lexer\Scanner\Tuple $tuple, string $symbol) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'SYMBOL') && ((string) $tuple->token === $symbol));
		}

		protected function IsTerminal(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'TERMINAL'));
		}

		protected function IsVariableTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE'));
		}

		protected function MapTerm() : VS\Parser\MapTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsMapTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$entries = array();
			$entries[] = $this->Symbol('{');
			if (!$this->IsSymbol($tuple, '}')) {
				$key = $this->StringTerm();
				$this->Symbol(':');
				$val = $this->Term();
				$entries[] = Common\Tuple::box2($key, $val);
			}
			while (true) {
				if ($this->IsSymbol($tuple, '}')) {
					$this->Symbol('}');
					break;
				}
				$this->Symbol(',');
				$key = $this->StringTerm();
				$this->Symbol(':');
				$val = $this->Term();
				$entries[] = Common\Tuple::box2($key, $val);
			}
			return new VS\Parser\MapTerm($entries);
		}

		protected function NullTerm() : VS\Parser\NullTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsNullTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\NullTerm();
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm() : VS\Parser\RealTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsRealTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\RealTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function RunStatement() : VS\Parser\RunStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol('(');
			$args[] = $this->VariableKey();
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(')');
			$this->Terminal();
			return new VS\Parser\RunStatement($args);
		}

		protected function Statement() : VS\Parser\Statement {
			$tuple = $this->scanner->current();
			if ($this->IsStatement($tuple, 'def')) {
				return $this->DefStatement();
			}
			if ($this->IsStatement($tuple, 'eval')) {
				return $this->EvalStatement();
			}
			if ($this->IsStatement($tuple, 'run')) {
				return $this->RunStatement();
			}
			$this->SyntaxError($tuple);
		}

		protected function StringTerm() : VS\Parser\StringTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsStringTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\StringTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Symbol(string $symbol) : VS\Parser\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, $symbol)) {
				$this->SyntaxError($tuple);
			}
			$symbol = new VS\Parser\Symbol((string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function SyntaxError(?Lexer\Scanner\Tuple $tuple) : void {
			if (is_null($tuple)) {
				throw new Throwable\Parse\Exception('Syntax error. Statement is incomplete.');
			}
			else {
				throw new Throwable\Parse\Exception('Syntax error. Unexpected token \':token\' of type \':type\' encountered.', array(':token' => (string) $tuple->token, ':type' => (string) $tuple->type));
			}
		}

		protected function Term() : VS\Parser\Term {
			$tuple = $this->scanner->current();
			if ($this->IsArrayTerm($tuple)) {
				return $this->ArrayTerm();
			}
			if ($this->IsBooleanTerm($tuple)) {
				return $this->BooleanTerm();
			}
			if ($this->IsIntegerTerm($tuple)) {
				return $this->IntegerTerm();
			}
			if ($this->IsMapTerm($tuple)) {
				return $this->MapTerm();
			}
			if ($this->IsNullTerm($tuple)) {
				return $this->NullTerm();
			}
			if ($this->IsRealTerm($tuple)) {
				return $this->RealTerm();
			}
			if ($this->IsStringTerm($tuple)) {
				return $this->StringTerm();
			}
			if ($this->IsVariableTerm($tuple)) {
				return $this->VariableTerm();
			}
			$this->SyntaxError($tuple);
		}

		protected function Terminal() : VS\Parser\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->IsTerminal($tuple)) {
				$this->SyntaxError($tuple);
			}
			$token = (string) $tuple->token;
			$terminal = new VS\Parser\Terminal($token);
			$this->scanner->next();
			return $terminal;
		}

		protected function TermOption(...$terms) : VS\Parser\Term {
			$tuple = $this->scanner->current();
			foreach ($terms as $term) {
				$IsA = 'Is' . $term;
				if ($this->{$IsA}($tuple)) {
					return $this->{$term}();
				}
			}
			$this->SyntaxError($tuple);
		}

		protected function VariableKey() : VS\Parser\VariableKey {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$key = new VS\Parser\VariableKey((string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function VariableTerm() : VS\Parser\VariableTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\VariableTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

	}

}