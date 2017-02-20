<?php

declare(strict_types = 1);

namespace Unicity\VS\Automaton {

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

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Literal('"'));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Variable());
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Keyword(
				'def', 'run', 'eval', // instructions
				'false', 'true', // booleans
				'null'
			));

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

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Unknown());

			$this->scanner->addIgnorable(Lexer\Scanner\TokenType::whitespace());
		}

		public function start() : void {
			while ($this->scanner->next()) {
				$tuple = $this->scanner->current();
				$token = (string) $tuple->token;
				$type = (string) $tuple->type;
				switch ($type) {
					case 'KEYWORD':
						switch ($token) {
							case 'def':
								$this->DefStatement()->accept0();
								break;
							case 'run':
								$this->RunStatement()->accept0();
								break;
							default:
								$this->SyntaxError($tuple);
								break;
						}
						break;
					default:
						$this->SyntaxError($tuple);
						break;
				}
			}
		}

		protected function ArrayTerm() : VS\Automaton\ArrayTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, '[')) {
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
			return new VS\Automaton\ArrayTerm($terms);
		}

		protected function DefStatement() : VS\Automaton\DefStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol('(');
			$args[] = $this->VariableKey();
			$this->Symbol(',');
			$args[] = $this->Term();
			$this->Symbol(')');
			$this->Terminal();
			return new VS\Automaton\DefStatement($args);
		}

		protected function FalseTerm() : VS\Automaton\FalseTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsFalseTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\FalseTerm();
			$this->scanner->next();
			return $term;
		}

		protected function IntegerTerm() : VS\Automaton\IntegerTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsIntegerTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\IntegerTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function IsFalseTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'false'));
		}

		protected function IsIntegerTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:INTEGER'));
		}

		protected function IsNullTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'null'));
		}

		protected function IsRealTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:REAL'));
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

		protected function IsTrueTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'true'));
		}

		protected function IsVariableTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE'));
		}

		protected function MapTerm() : VS\Automaton\MapTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, '{')) {
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
			return new VS\Automaton\MapTerm($entries);
		}

		protected function NullTerm() : VS\Automaton\NullTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsNullTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\NullTerm();
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm() : VS\Automaton\RealTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsRealTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\RealTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}


		protected function RunStatement() : VS\Automaton\RunStatement {
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
			return new VS\Automaton\RunStatement($args);
		}

		protected function StringTerm() : VS\Automaton\StringTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsStringTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\StringTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Symbol(string $symbol) : VS\Automaton\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, $symbol)) {
				$this->SyntaxError($tuple);
			}
			$symbol = new VS\Automaton\Symbol((string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function SyntaxError(Lexer\Scanner\Tuple $tuple = null) : void {
			if (is_null($tuple)) {
				throw new Throwable\Parse\Exception('Syntax error. Missing token.');
			}
			else {
				throw new Throwable\Parse\Exception('Syntax error. Unexpected token ":token" of type ":type" encountered.', array(':token' => (string) $tuple->token, ':type' => (string) $tuple->type));
			}
		}

		protected function Term() : VS\Automaton\Term {
			$tuple = $this->scanner->current();
			if ($this->IsFalseTerm($tuple)) {
				return $this->FalseTerm();
			}
			if ($this->IsIntegerTerm($tuple)) {
				return $this->IntegerTerm();
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
			if ($this->IsSymbol($tuple, '[')) {
				return $this->ArrayTerm();
			}
			if ($this->IsSymbol($tuple, '{')) {
				return $this->MapTerm();
			}
			if ($this->IsTrueTerm($tuple)) {
				return $this->TrueTerm();
			}
			if ($this->IsVariableTerm($tuple)) {
				return $this->VariableTerm();
			}
			$this->SyntaxError($tuple);
		}

		protected function Terminal() : VS\Automaton\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->IsTerminal($tuple)) {
				$this->SyntaxError($tuple);
			}
			$token = (string) $tuple->token;
			$terminal = new VS\Automaton\Terminal($token);
			$this->scanner->next();
			return $terminal;
		}

		protected function TrueTerm() : VS\Automaton\TrueTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsTrueTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\TrueTerm();
			$this->scanner->next();
			return $term;
		}

		protected function VariableKey() : VS\Automaton\VariableKey {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$key = new VS\Automaton\VariableKey((string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function VariableTerm() : VS\Automaton\VariableTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Automaton\VariableTerm((string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

	}

}