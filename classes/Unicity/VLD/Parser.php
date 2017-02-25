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
		 * This variable stores the config for how errors are handled.
		 *
		 * @access protected
		 * @var array
		 */
		protected $error_handler;

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
		public function __construct(IO\Reader $reader, array $error_handler = array()) {
			$this->error_handler = array_merge([
				'logs' => ['stderr'],
				'throw' => false,
			], $error_handler);

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

			$this->scanner->addRule(new VLD\Scanner\TokenRule\ArrayVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\BlockVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\BooleanVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\MapVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\MixedVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\NumberVariable());
			$this->scanner->addRule(new VLD\Scanner\TokenRule\StringVariable());

			$this->scanner->addRule(new Lexer\Scanner\TokenRule\Keyword([
				'eval', 'include', 'install', 'set', // simple statements
				'do', 'is', 'not', 'run', 'select', // complex statements
				'false', 'true', // boolean values
				'null', // null value
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

		#region Productions

		protected function ArrayTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayTerm {
			$tuple = $this->scanner->current();
			if (!$this->isArrayTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$terms = array();
			$this->Symbol($context, '[');
			if (!$this->isSymbol($this->scanner->current(), ']')) {
				$terms[] = $this->Term($context);
				while (!$this->isSymbol($this->scanner->current(), ']')) {
					$this->Symbol($context, ',');
					$terms[] = $this->Term($context);
				}
			}
			$this->Symbol($context, ']');
			return new VLD\Parser\Definition\ArrayTerm($context, $terms);
		}

		protected function BooleanTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanTerm {
			$tuple = $this->scanner->current();
			if (!$this->isBooleanTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\BooleanTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Block(VLD\Parser\Context $context) : VLD\Parser\Definition\Block {
			if ($this->isVariableBlockTerm($this->scanner->current())) {
				return $this->BlockVariable($context);
			}
			return $this->BlockTerm($context);
		}

		protected function BlockTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockTerm { // call "Block" method
			$this->Symbol($context, '{');
			$statements = array();
			while (!$this->isSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$term = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->Symbol($context, '}');
			return $term;
		}

		protected function BlockVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockVariable { // call "Block" method
			$tuple = $this->scanner->current();
			$variable = new VLD\Parser\Definition\BlockVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $variable;
		}

		protected function DoStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\DoStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$block = $this->Block($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\DoStatement($context, $args, $block);
		}

		protected function EvalStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\EvalStatement($context, $args);
		}

		protected function IsStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IsStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm', 'VariableArrayTerm', 'VariableStringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$block = $this->Block($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\IsStatement($context, $args, $block);
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
			if (!$this->isIntegerTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\IntegerTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function MapTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\MapTerm {
			$tuple = $this->scanner->current();
			if (!$this->isMapTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$entries = array();
			$this->Symbol($context, '{');
			if (!$this->isSymbol($this->scanner->current(), '}')) {
				$key = $this->StringTerm($context);
				$this->Symbol($context, ':');
				$val = $this->Term($context);
				$entries[] = Common\Tuple::box2($key, $val);
				while (!$this->isSymbol($this->scanner->current(), '}')) {
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
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$block = $this->Block($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\NotStatement($context, $args, $block);
		}

		protected function NullTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\NullTerm {
			$tuple = $this->scanner->current();
			if (!$this->isNullTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\NullTerm($context);
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\RealTerm {
			$tuple = $this->scanner->current();
			if (!$this->isRealTerm($tuple)) {
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
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->Term($context);
			}
			$this->Symbol($context, ')');
			$block = $this->Block($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\RunStatement($context, $args, $block);
		}

		protected function SelectStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SelectStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$args[] = $this->Terms($context, 'StringTerm', 'VariableStringTerm');
			}
			$this->Symbol($context, ')');
			$block = $this->Block($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\SelectStatement($context, $args, $block);
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

			// Simple Statements
			if ($this->isKeyword($tuple, 'eval')) {
				return $this->EvalStatement($context);
			}
			if ($this->isKeyword($tuple, 'include')) {
				return $this->IncludeStatement($context);
			}
			if ($this->isKeyword($tuple, 'install')) {
				return $this->InstallStatement($context);
			}
			if ($this->isKeyword($tuple, 'set')) {
				return $this->SetStatement($context);
			}

			// Complex Statements
			if ($this->isKeyword($tuple, 'do')) {
				return $this->DoStatement($context);
			}
			if ($this->isKeyword($tuple, 'is')) {
				return $this->IsStatement($context);
			}
			if ($this->isKeyword($tuple, 'not')) {
				return $this->NotStatement($context);
			}
			if ($this->isKeyword($tuple, 'run')) {
				return $this->RunStatement($context);
			}
			if ($this->isKeyword($tuple, 'select')) {
				return $this->SelectStatement($context);
			}

			// Error Handling
			$this->SyntaxError($tuple);
		}

		protected function StringTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\StringTerm {
			$tuple = $this->scanner->current();
			if (!$this->isStringTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\StringTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Symbol(VLD\Parser\Context $context, string $symbol) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->isSymbol($tuple, $symbol)) {
				$this->SyntaxError($tuple);
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function SyntaxError(?Lexer\Scanner\Tuple $tuple) : void {
			if (is_null($tuple)) {
				$this->WriteError('VLD Parse error: syntax error, unexpected end of file.');
			}
			else {
				$this->WriteError('VLD Parse error: syntax error, unexpected token \':token\' of type \':type\' encountered at :index.', array(':index' => $tuple->index, ':token' => (string) $tuple->token, ':type' => (string) $tuple->type));
			}
		}

		protected function Term(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isArrayTerm($tuple)) {
				return $this->ArrayTerm($context);
			}
			if ($this->isBooleanTerm($tuple)) {
				return $this->BooleanTerm($context);
			}
			if ($this->isIntegerTerm($tuple)) {
				return $this->IntegerTerm($context);
			}
			if ($this->isMapTerm($tuple)) {
				return $this->MapTerm($context);
			}
			if ($this->isNullTerm($tuple)) {
				return $this->NullTerm($context);
			}
			if ($this->isRealTerm($tuple)) {
				return $this->RealTerm($context);
			}
			if ($this->isStringTerm($tuple)) {
				return $this->StringTerm($context);
			}
			if ($this->isVariableTerm($tuple)) {
				return $this->VariableTerm($context);
			}
			$this->SyntaxError($tuple);
		}

		protected function Terminal(VLD\Parser\Context $context) : VLD\Parser\Definition\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->isTerminal($tuple)) {
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

		protected function VariableKey(VLD\Parser\Context $context) : VLD\Parser\Definition\KeyVariable {
			$tuple = $this->scanner->current();
			if (!$this->isVariableTerm($tuple) && !$this->isVariableBlockTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$key = new VLD\Parser\Definition\KeyVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function VariableTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\TermVariable {
			$tuple = $this->scanner->current();
			if (!$this->isVariableTerm($tuple)) {
				$this->SyntaxError($tuple);
			}
			$term = new VLD\Parser\Definition\TermVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function WriteError(string $message, array $variables = null) : void {
			$message = empty($variables) ? (string) $message : strtr((string) $message, $variables);
			$logs = $this->error_handler['logs'];
			foreach ($logs as $log) {
				switch ($log) {
					case 'stderr':
						//fwrite(STDERR, $message . PHP_EOL);
						error_log($message, 0);
						break;
					case 'syslog':
						openlog('Unknown', LOG_CONS, LOG_USER);
						syslog(LOG_ERR, $message);
						break;
				}
			}
			if ($this->error_handler['throw']) {
				throw new Throwable\Parse\Exception($message);
			}
			exit();
		}

		#endregion

		#region Helpers

		protected function isArrayTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '[');
		}

		protected function isBooleanTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && in_array((string) $tuple->token, ['false', 'true']));
		}

		protected function isIntegerTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:INTEGER'));
		}

		protected function isKeyword(Lexer\Scanner\Tuple $tuple, string $identifier) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === $identifier));
		}

		protected function isMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '{');
		}

		protected function isNullTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'null'));
		}

		protected function isRealTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:REAL'));
		}

		protected function isStringTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'LITERAL'));
		}

		protected function isSymbol(Lexer\Scanner\Tuple $tuple, string $symbol) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'SYMBOL') && ((string) $tuple->token === $symbol));
		}

		protected function isTerminal(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'TERMINAL'));
		}

		protected function isVariableTerm(Lexer\Scanner\Tuple $tuple) : bool {
			if (!is_null($tuple)) {
				$type = (string) $tuple->type;
				return preg_match('/^VARIABLE/', $type) && ($type !== 'VARIABLE:BLOCK');
			}
			return false;
		}

		protected function isVariableArrayTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:ARRAY'));
		}

		protected function isVariableBlockTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:BLOCK'));
		}

		protected function isVariableBooleanTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:BOOLEAN'));
		}

		protected function isVariableMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MAP'));
		}

		protected function isVariableMixedTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MIXED'));
		}

		protected function isVariableNumberTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:NUMBER'));
		}

		protected function isVariableStringTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:STRING'));
		}

		#endregion

	}

}