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

	use \Unicity\Common;
	use \Unicity\Config;
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
		 * @static
		 * @var array
		 */
		protected static $error_handler = null;

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
			$this->scanner = VLD\Scanner::factory($reader);
		}

		public function read(VLD\Parser\Context $context) : array {
			$statements = array();
			while ($this->scanner->next()) {
				$statements[] = $this->Statement($context);
			}
			return $statements;
		}

		public function run(Common\HashMap $input) : Common\IMap {
			$context = new VLD\Parser\Context($input);
			$control = new VLD\Parser\Definition\SeqControl($context, null, $this->read($context));
			return $control->get()->toMap();
		}

		#region Productions

		#region Symbols

		protected function Symbol(VLD\Parser\Context $context, string $symbol) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->isSymbol($tuple, $symbol)) {
				$this->SyntaxError();
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function Terminal(VLD\Parser\Context $context) : VLD\Parser\Definition\Terminal {
			$tuple = $this->scanner->current();
			if (!$this->isTerminal($tuple)) {
				$this->SyntaxError();
			}
			$terminal = new VLD\Parser\Definition\Terminal($context, (string) $tuple->token);
			$this->scanner->next();
			return $terminal;
		}

		#endregion

		#region Terms

		protected function ArrayKey(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayKey {
			$tuple = $this->scanner->current();
			if (!$this->isArrayVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\ArrayKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function ArrayTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			if ($this->isArrayVariable($this->scanner->current())) {
				return $this->ArrayVariable($context);
			}
			$terms = array();
			$this->Symbol($context, '[');
			if (!$this->isSymbol($this->scanner->current(), ']')) {
				$terms[] = $this->MixedTerm($context);
				while (!$this->isSymbol($this->scanner->current(), ']')) {
					$this->Symbol($context, ',');
					$terms[] = $this->MixedTerm($context);
				}
			}
			$this->Symbol($context, ']');
			return new VLD\Parser\Definition\ArrayTerm($context, $terms);
		}

		protected function ArrayVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayVariable {
			$tuple = $this->scanner->current();
			if (!$this->isArrayVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\ArrayVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function BlockKey(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockKey {
			$tuple = $this->scanner->current();
			if (!$this->isBlockKey($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\BlockKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function BlockRef(VLD\Parser\Context $context) : VLD\Parser\Definition\Block {
			$tuple = $this->scanner->current();
			if (!$this->isBlockRef($tuple)) {
				$this->SyntaxError();
			}
			if ($this->isStringVariable($tuple)) {
				$variable = new VLD\Parser\Definition\BlockVariable($context, (string) $tuple->token);
				$this->scanner->next();
				return $variable;
			}
			$variable = new VLD\Parser\Definition\BlockRef($context, (string) $tuple->token);
			$this->scanner->next();
			return $variable;
		}

		protected function BlockTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Block {
			$tuple = $this->scanner->current();
			if ($this->isBlockRef($tuple)) {
				return $this->BlockRef($context);
			}
			if ($this->isBlockVariable($tuple)) {
				return $this->BlockVariable($context);
			}
			$this->Symbol($context, '{');
			$statements = array();
			while (!$this->isSymbol($this->scanner->current(), '}')) {
				$statements[] = $this->Statement($context);
			}
			$term = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->Symbol($context, '}');
			return $term;
		}

		protected function BlockVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockVariable {
			$tuple = $this->scanner->current();
			if (!$this->isBlockVariable($tuple)) {
				$this->SyntaxError();
			}
			$variable = new VLD\Parser\Definition\BlockVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $variable;
		}

		protected function BooleanKey(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanKey {
			$tuple = $this->scanner->current();
			if (!$this->isBooleanVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\BooleanKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function BooleanTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isBooleanVariable($tuple)) {
				return $this->BooleanVariable($context);
			}
			$term = new VLD\Parser\Definition\BooleanTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function BooleanVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanVariable {
			$tuple = $this->scanner->current();
			if (!$this->isBooleanVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\BooleanVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function IntegerTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isIntegerTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\IntegerTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function MapKey(VLD\Parser\Context $context) : VLD\Parser\Definition\MapKey {
			$tuple = $this->scanner->current();
			if (!$this->isMapVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\MapKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function MapTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			if ($this->isMapVariable($this->scanner->current())) {
				return $this->MapVariable($context);
			}
			$entries = array();
			$this->Symbol($context, '{');
			if (!$this->isSymbol($this->scanner->current(), '}')) {
				$key = $this->StringTerm($context);
				$this->Symbol($context, ':');
				$val = $this->MixedTerm($context);
				$entries[] = Common\Tuple::box2($key, $val);
				while (!$this->isSymbol($this->scanner->current(), '}')) {
					$this->Symbol($context, ',');
					$key = $this->StringTerm($context);
					$this->Symbol($context, ':');
					$val = $this->MixedTerm($context);
					$entries[] = Common\Tuple::box2($key, $val);
				}
			}
			$this->Symbol($context, '}');
			return new VLD\Parser\Definition\MapTerm($context, $entries);
		}

		protected function MapVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\MapVariable {
			$tuple = $this->scanner->current();
			if (!$this->isMapVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\MapVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function MixedKey(VLD\Parser\Context $context) : VLD\Parser\Definition\MixedKey {
			$tuple = $this->scanner->current();
			if (!$this->isMixedKey($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\MixedKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function MixedTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isArrayTerm($tuple)) {
				return $this->ArrayTerm($context);
			}
			if ($this->isBooleanTerm($tuple)) {
				return $this->BooleanTerm($context);
			}
			if ($this->isMapTerm($tuple)) {
				return $this->MapTerm($context);
			}
			if ($this->isNullTerm($tuple)) {
				return $this->NullTerm($context);
			}
			if ($this->isNumberTerm($tuple)) {
				return $this->NumberTerm($context);
			}
			if ($this->isStringTerm($tuple)) {
				return $this->StringTerm($context);
			}
			return $this->MixedVariable($context);
		}

		protected function MixedVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\MixedVariable {
			$tuple = $this->scanner->current();
			if (!$this->isMixedVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\MixedVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function NullTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isNullTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\NullTerm($context);
			$this->scanner->next();
			return $term;
		}

		protected function NumberKey(VLD\Parser\Context $context) : VLD\Parser\Definition\NumberKey {
			$tuple = $this->scanner->current();
			if (!$this->isNumberVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\NumberKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function NumberTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isIntegerTerm($tuple)) {
				return $this->IntegerTerm($context);
			}
			if ($this->isRealTerm($tuple)) {
				return $this->RealTerm($context);
			}
			return $this->NumberVariable($context);
		}

		protected function NumberVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\NumberVariable {
			$tuple = $this->scanner->current();
			if (!$this->isNumberVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\NumberVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function RealTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isRealTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\RealTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function StringKey(VLD\Parser\Context $context) : VLD\Parser\Definition\StringKey {
			$tuple = $this->scanner->current();
			if (!$this->isStringVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\StringKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function StringTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isStringVariable($tuple)) {
				return $this->StringVariable($context);
			}
			$term = new VLD\Parser\Definition\StringTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function StringVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\StringVariable {
			$tuple = $this->scanner->current();
			if (!$this->isStringVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\StringVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function Terms(VLD\Parser\Context $context, ...$terms) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			foreach ($terms as $term) {
				$IsA = 'Is' . $term;
				if ($this->{$IsA}($tuple)) {
					return $this->{$term}($context);
				}
			}
			$this->SyntaxError();
		}

		#endregion

		#region Statements

		#region General

		protected function Statement(VLD\Parser\Context $context) : VLD\Parser\Definition\Statement {
			$tuple = $this->scanner->current();

			// Simple Statements
			if ($this->isKeyword($tuple, 'eval')) {
				return $this->EvalStatement($context);
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
			$this->SyntaxError();
		}

		#endregion

		#region Simple Statements

		protected function EvalStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->MixedTerm($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\EvalStatement($context, $args);
		}

		protected function InstallStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\InstallStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\InstallStatement($context, $args);
		}

		protected function SetStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SetStatement {
			$this->scanner->next();
			$entry = array();
			$this->Symbol($context, '(');
			$tuple = $this->scanner->current();
			if ($this->isArrayKey($tuple)) {
				$entry[] = $this->ArrayKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->ArrayTerm($context);
			}
			else if ($this->isBlockKey($tuple)) {
				$entry[] = $this->BlockKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->BlockTerm($context);
			}
			else if ($this->isBooleanKey($tuple)) {
				$entry[] = $this->BooleanKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->BooleanTerm($context);
			}
			else if ($this->isMapKey($tuple)) {
				$entry[] = $this->MapKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->MapTerm($context);
			}
			else if ($this->isMixedKey($tuple)) {
				$entry[] = $this->MixedKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->MixedTerm($context);
			}
			else if ($this->isNumberKey($tuple)) {
				$entry[] = $this->NumberKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->NumberTerm($context);
			}
			else if ($this->isStringKey($tuple)) {
				$entry[] = $this->StringKey($context);
				$this->Symbol($context, ',');
				$entry[] = $this->StringTerm($context);
			}
			$this->Symbol($context, ')');
			$this->Terminal($context);
			return new VLD\Parser\Definition\SetStatement($context, $entry);
		}

		#endregion

		#region Complex Statements

		protected function DoStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\DoStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->MixedTerm($context);
			}
			$this->Symbol($context, ')');
			$block = $this->BlockTerm($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\DoStatement($context, $args, $block);
		}

		protected function IsStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IsStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->MixedTerm($context);
			}
			$this->Symbol($context, ')');
			$block = $this->BlockTerm($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\IsStatement($context, $args, $block);
		}

		protected function NotStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\NotStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			$this->Symbol($context, ',');
			$args[] = $this->Terms($context, 'ArrayTerm', 'StringTerm');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->MixedTerm($context);
			}
			$this->Symbol($context, ')');
			$block = $this->BlockTerm($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\NotStatement($context, $args, $block);
		}

		protected function RunStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			$args[] = $this->StringTerm($context);
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$this->Symbol($context, ',');
				$args[] = $this->MixedTerm($context);
			}
			$this->Symbol($context, ')');
			$block = $this->BlockTerm($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\RunStatement($context, $args, $block);
		}

		protected function SelectStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SelectStatement {
			$this->scanner->next();
			$args = array();
			$this->Symbol($context, '(');
			if (!$this->isSymbol($this->scanner->current(), ')')) {
				$args[] = $this->StringTerm($context);
			}
			$this->Symbol($context, ')');
			$block = $this->BlockTerm($context);
			$this->Terminal($context);
			return new VLD\Parser\Definition\SelectStatement($context, $args, $block);
		}

		#endregion

		#endregion

		#region Error Handling

		protected function SyntaxError() : void {
			$tuple = $this->scanner->current();
			if (is_null($tuple)) {
				$this->WriteError('VLD Parse error: syntax error, unexpected end of file.');
			}
			else {
				$this->WriteError('VLD Parse error: syntax error, unexpected token \':token\' of type \':type\' encountered at :index.', array(':index' => $tuple->index, ':token' => (string) $tuple->token, ':type' => (string) $tuple->type));
			}
		}

		protected function WriteError(string $message, array $variables = null) : void {
			if (static::$error_handler === null) {
				$directory = dirname(__FILE__);
				static::$error_handler = Config\Inc\Reader::load(new IO\File($directory . '/Parser/Config.php'))->read();
			}
			$message = empty($variables) ? (string) $message : strtr((string) $message, $variables);
			$logs = static::$error_handler['logs'];
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
			if (static::$error_handler['throw']) {
				throw new Throwable\Parse\Exception($message);
			}
			exit();
		}

		#endregion

		#endregion

		#region Helpers

		protected function isArrayKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:ARRAY'));
		}

		protected function isArrayTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '[') || $this->isArrayVariable($tuple);
		}

		protected function isArrayVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isArrayKey($tuple);
		}

		protected function isBlockKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:BLOCK'));
		}

		protected function isBlockRef(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isStringTerm($tuple);
		}

		protected function isBlockTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '{') || $this->isBlockRef($tuple) || $this->isBlockVariable($tuple);
		}

		protected function isBlockVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isBlockKey($tuple);
		}

		protected function isBooleanKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:BOOLEAN'));
		}

		protected function isBooleanTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && in_array((string) $tuple->token, ['false', 'true']))  || $this->isBooleanVariable($tuple);;
		}

		protected function isBooleanVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isBooleanKey($tuple);
		}

		protected function isIntegerTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:INTEGER'));
		}

		protected function isKeyword(Lexer\Scanner\Tuple $tuple, string $identifier) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === $identifier));
		}

		protected function isMapKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MAP'));
		}

		protected function isMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '{') || $this->isMapVariable($tuple);
		}

		protected function isMapVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isMapKey($tuple);
		}

		protected function isMixedKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MIXED'));
		}

		protected function isMixedTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isArrayTerm($tuple) || $this->isBooleanTerm($tuple) || $this->isIntegerTerm($tuple) || $this->isMapTerm($tuple) || $this->isNullTerm($tuple) || $this->isRealTerm($tuple) || $this->isStringTerm($tuple) || $this->isMixedVariable($tuple);
		}

		protected function isMixedVariable(Lexer\Scanner\Tuple $tuple) : bool {
			if (!is_null($tuple)) {
				$type = (string) $tuple->type;
				return preg_match('/^VARIABLE/', $type) && ($type !== 'VARIABLE:BLOCK');
			}
			return false;
		}

		protected function isNullTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === 'null'));
		}

		protected function isNumberKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:NUMBER'));
		}

		protected function isNumberTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isIntegerTerm($tuple) || $this->isRealTerm($tuple) || $this->isNumberVariable($tuple);
		}

		protected function isNumberVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isNumberKey($tuple);
		}

		protected function isRealTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:REAL'));
		}

		protected function isStringKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:STRING'));
		}

		protected function isStringTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'LITERAL')) || $this->isStringVariable($tuple);
		}

		protected function isStringVariable(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isStringKey($tuple);
		}

		protected function isSymbol(Lexer\Scanner\Tuple $tuple, string $symbol) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'SYMBOL') && ((string) $tuple->token === $symbol));
		}

		protected function isTerminal(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'TERMINAL'));
		}

		#endregion

	}

}