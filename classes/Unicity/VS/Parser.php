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

	use \Unicity\BT;
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
				'eval', 'install', 'run', 'select', 'set', // statements
				'false', 'true', // booleans
				'null',
			]));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Unknown());

			$this->scanner->addIgnorable(Lexer\Scanner\TokenType::whitespace());
		}

		public function run(Common\HashMap $input) : Common\IMap {
			$feedback = new VS\Validation\Feedback();
			$context = new VS\Parser\Context(new BT\Entity([
				'components' => $input,
				'entity_id' => 0,
			]));
			$this->scanner->next();
			while (!is_null($this->scanner->current())) {
				$result = $this->Statement($context)->get();
				$feedback->addRecommendations($result);
				if ($result->getNumberOfViolations() > 0) {
					$feedback->addViolations($result);
					break;
				}
			}
			return $feedback->toMap();
			/*
			while ($this->scanner->next()) {
				var_dump($this->scanner->current());
			}
			exit();
			*/
		}

		protected function ArrayTerm(VS\Parser\Context $context) : VS\Parser\Definition\ArrayTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsArrayTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$terms = array();
			$this->Symbol($context, '[');
			if (!$this->IsSymbol($this->scanner->current(), ']')) {
				$terms[] = $this->Term($context);
				while (!$this->IsSymbol($this->scanner->current(), ']')) {
					$this->Symbol($context, ',');
					$terms[] = $this->Term($context);
				}
			}
			$this->Symbol($context, ']');
			return new VS\Parser\Definition\ArrayTerm($context, $terms);
		}

		protected function BooleanTerm(VS\Parser\Context $context) : VS\Parser\Definition\BooleanTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsBooleanTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\BooleanTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function EvalStatement(VS\Parser\Context $context) : VS\Parser\Definition\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->TermOption($context, 'StringTerm', 'VariableTerm');
			$this->Symbol($context, ',');
			$args[] = $this->TermOption($context, 'ArrayTerm', 'StringTerm', 'VariableTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VS\Parser\Definition\EvalStatement($context, $args);
		}

		protected function InstallStatement(VS\Parser\Context $context) : VS\Parser\Definition\InstallStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->TermOption($context, 'StringTerm', 'VariableTerm');
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VS\Parser\Definition\InstallStatement($context, $args);
		}

		protected function IntegerTerm(VS\Parser\Context $context) : VS\Parser\Definition\IntegerTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsIntegerTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\IntegerTerm($context, (string) $tuple->token);
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

		protected function MapTerm(VS\Parser\Context $context) : VS\Parser\Definition\MapTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsMapTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$entries = array();
			$this->Symbol($context, '{');
			if (!$this->IsSymbol($this->scanner->current(), '}')) {
				$key = $this->StringTerm($context);
				$this->Symbol($context, ':');
				$val = $this->Term($context);
				$entries[] = Common\Tuple::box2($key, $val);
				while (!$this->IsSymbol($this->scanner->current(), '}')) {
					$this->Symbol($context, ',');
					$key = $this->StringTerm($context);
					$this->Symbol($context, ':');
					$val = $this->Term($context);
					$entries[] = Common\Tuple::box2($key, $val);
				}
			}
			$this->Symbol($context, '}');
			return new VS\Parser\Definition\MapTerm($context, $entries);
		}

		protected function NullTerm(VS\Parser\Context $context) : VS\Parser\Definition\NullTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsNullTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\NullTerm($context);
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm(VS\Parser\Context $context) : VS\Parser\Definition\RealTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsRealTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\RealTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function RunStatement(VS\Parser\Context $context) : VS\Parser\Definition\RunStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->TermOption($context, 'StringTerm', 'VariableTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VS\Parser\Definition\RunStatement($context, $args);
		}

		protected function SelectStatement(VS\Parser\Context $context) : VS\Parser\Definition\SelectStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$args[] = $this->TermOption($context, 'StringTerm', 'VariableTerm');
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VS\Parser\Definition\SelectStatement($context, $args, $statements);
		}

		protected function SetStatement(VS\Parser\Context $context) : VS\Parser\Definition\SetStatement {
			$this->scanner->next();
			$entry = array();
			$this->Symbol($context, '(');
			$entry[] = $this->VariableKey($context);
			$this->Symbol($context, ',');
			$entry[] = $this->Term($context);
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VS\Parser\Definition\SetStatement($context, $entry);
		}

		protected function Statement(VS\Parser\Context $context) : VS\Parser\Definition\Statement {
			$tuple = $this->scanner->current();
			if ($this->IsStatement($tuple, 'eval')) {
				return $this->EvalStatement($context);
			}
			if ($this->IsStatement($tuple, 'install')) {
				return $this->InstallStatement($context);
			}
			if ($this->IsStatement($tuple, 'run')) {
				return $this->RunStatement($context);
			}
			if ($this->IsStatement($tuple, 'select')) {
				return $this->SelectStatement($context);
			}
			if ($this->IsStatement($tuple, 'set')) {
				return $this->SetStatement($context);
			}
			$this->SyntaxError($tuple);
		}

		protected function StringTerm(VS\Parser\Context $context) : VS\Parser\Definition\StringTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsStringTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\StringTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Symbol(VS\Parser\Context $context, string $symbol) : VS\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, $symbol)) {
				$this->SyntaxError($tuple);
			}
			$symbol = new VS\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function SyntaxError(?Lexer\Scanner\Tuple $tuple) : void {
			if (is_null($tuple)) {
				throw new Throwable\Parse\Exception('Syntax error. Statement is incomplete.');
			}
			else {
				throw new Throwable\Parse\Exception('Syntax error. Unexpected token \':token\' of type \':type\' encountered at :index.', array(':index' => $tuple->index, ':token' => (string) $tuple->token, ':type' => (string) $tuple->type));
			}
		}

		protected function Term(VS\Parser\Context $context) : VS\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->IsArrayTerm($tuple)) {
				return $this->ArrayTerm($context);
			}
			if ($this->IsBooleanTerm($tuple)) {
				return $this->BooleanTerm($context);
			}
			if ($this->IsIntegerTerm($tuple)) {
				return $this->IntegerTerm($context);
			}
			if ($this->IsMapTerm($tuple)) {
				return $this->MapTerm($context);
			}
			if ($this->IsNullTerm($tuple)) {
				return $this->NullTerm($context);
			}
			if ($this->IsRealTerm($tuple)) {
				return $this->RealTerm($context);
			}
			if ($this->IsStringTerm($tuple)) {
				return $this->StringTerm($context);
			}
			if ($this->IsVariableTerm($tuple)) {
				return $this->VariableTerm($context);
			}
			$this->SyntaxError($tuple);
		}

		protected function Terminal(VS\Parser\Context $context) : VS\Parser\Definition\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->IsTerminal($tuple)) {
				$this->SyntaxError($tuple);
			}
			$terminal = new VS\Parser\Definition\Terminal($context, (string) $tuple->token);
			$this->scanner->next();
			return $terminal;
		}

		protected function TermOption(VS\Parser\Context $context, ...$terms) : VS\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			foreach ($terms as $term) {
				$IsA = 'Is' . $term;
				if ($this->{$IsA}($tuple)) {
					return $this->{$term}($context);
				}
			}
			$this->SyntaxError($tuple);
		}

		protected function VariableKey(VS\Parser\Context $context) : VS\Parser\Definition\VariableKey {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$key = new VS\Parser\Definition\VariableKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function VariableTerm(VS\Parser\Context $context) : VS\Parser\Definition\VariableTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VS\Parser\Definition\VariableTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

	}

}