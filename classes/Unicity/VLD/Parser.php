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

	use \Unicity\BT;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Lexer;
	use \Unicity\Throwable;
	use \Unicity\VLD;

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
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\BlockComment('/*', '*/'));
			$this->scanner->addRule(new Lexer\Scanner\TokenRule\EOLComment('`'));

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

			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableArray());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableBoolean());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableMap());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableMixed());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableNumber());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\VariableString());

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Keyword([
				'eval', 'include', 'install', 'set', // simple statements
				'if', 'not', 'run', 'select', 'tick', // complex statements
				'false', 'true', // booleans
				'null',
			]));

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Unknown());

			$this->scanner->addIgnorable(Lexer\Scanner\TokenType::whitespace());
		}

		public function run(Common\HashMap $input) : Common\IMap {
			/*
			while ($this->scanner->next()) {
				var_dump($this->scanner->current());
			}
			exit();
			*/
			$context = new VLD\Parser\Context(new BT\Entity([
				'components' => $input,
				'entity_id' => 0,
			]));
			$feedback = new VLD\Parser\Feedback($context->getPath());
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
		}

		protected function ArrayTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayTerm {
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
			return new VLD\Parser\Definition\ArrayTerm($context, $terms);
		}

		protected function BooleanTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsBooleanTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\BooleanTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function EvalStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\EvalStatement($context, $args);
		}

		protected function IfStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IfStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VLD\Parser\Definition\IfStatement($context, $args, $statements);
		}

		protected function IncludeStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IncludeStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\IncludeStatement($context, $args);
		}

		protected function InstallStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\InstallStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\InstallStatement($context, $args);
		}

		protected function IntegerTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\IntegerTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsIntegerTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\IntegerTerm($context, (string) $tuple->token);
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
			return (!is_null($tuple) && preg_match('/^VARIABLE/', (string) $tuple->type));
		}

		protected function IsVariableArrayTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:ARRAY'));
		}

		protected function IsVariableBooleanTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:BOOLEAN'));
		}

		protected function IsVariableMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MAP'));
		}

		protected function IsVariableMixedTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MIXED'));
		}

		protected function IsVariableNumberTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:NUMBER'));
		}

		protected function IsVariableStringTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:STRING'));
		}

		protected function MapTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\MapTerm {
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
			return new VLD\Parser\Definition\MapTerm($context, $entries);
		}

		protected function NotStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\NotStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VLD\Parser\Definition\NotStatement($context, $args, $statements);
		}

		protected function NullTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\NullTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsNullTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\NullTerm($context);
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\RealTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsRealTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\RealTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function RunStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VLD\Parser\Definition\RunStatement($context, $args, $statements);
		}

		protected function SelectStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SelectStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VLD\Parser\Definition\SelectStatement($context, $args, $statements);
		}

		protected function SetStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SetStatement {
			$this->scanner->next();
			$entry = array();
			$this->Symbol($context, '(');
			$entry[] = $this->VariableKey($context);
			$this->Symbol($context, ',');
			$entry[] = $this->Term($context);
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\SetStatement($context, $entry);
		}

		protected function Statement(VLD\Parser\Context $context) : VLD\Parser\Definition\Statement {
			$tuple = $this->scanner->current();
			if ($this->IsStatement($tuple, 'eval')) {
				return $this->EvalStatement($context);
			}
			if ($this->IsStatement($tuple, 'if')) {
				return $this->IfStatement($context);
			}
			if ($this->IsStatement($tuple, 'include')) {
				return $this->IncludeStatement($context);
			}
			if ($this->IsStatement($tuple, 'install')) {
				return $this->InstallStatement($context);
			}
			if ($this->IsStatement($tuple, 'not')) {
				return $this->NotStatement($context);
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
			if ($this->IsStatement($tuple, 'tick')) {
				return $this->TickStatement($context);
			}
			$this->SyntaxError($tuple);
		}

		protected function StringTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\StringTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsStringTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\StringTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Symbol(VLD\Parser\Context $context, string $symbol) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->IsSymbol($tuple, $symbol)) {
				$this->SyntaxError($tuple);
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
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

		protected function Term(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
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

		protected function Terminal(VLD\Parser\Context $context) : VLD\Parser\Definition\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->IsTerminal($tuple)) {
				$this->SyntaxError($tuple);
			}
			$terminal = new VLD\Parser\Definition\Terminal($context, (string) $tuple->token);
			$this->scanner->next();
			return $terminal;
		}

		protected function Terms(VLD\Parser\Context $context, ...$terms) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			foreach ($terms as $term) {
				$IsA = 'Is' . $term;
				if ($this->{$IsA}($tuple)) {
					return $this->{$term}($context);
				}
			}
			$this->SyntaxError($tuple);
		}

		protected function TickStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\TickStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->IsSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$statements = array();
			$this->Symbol($context, '{');
			while (!$this->IsSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$this->Symbol($context, '}');
			$this->Terminal($context);
			return new VLD\Parser\Definition\TickStatement($context, $args, $statements);
		}

		protected function VariableKey(VLD\Parser\Context $context) : VLD\Parser\Definition\VariableKey {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$key = new VLD\Parser\Definition\VariableKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function VariableTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\VariableTerm {
			$tuple = $this->scanner->current();
			if (!$this->IsVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\VariableTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

	}

}