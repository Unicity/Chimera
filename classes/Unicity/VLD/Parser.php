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
		 * This variable stores the interpreter's config file.
		 *
		 * @access protected
		 * @static
		 * @var array
		 */
		protected static $config = null;

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
			$this->scanner->next();
			while (!is_null($this->scanner->current())) {
				$statements[] = $this->getStatement($context);
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

		protected function getColon(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, ':');
		}

		protected function getComma(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, ',');
		}

		protected function getDo(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->isDoSentinel($tuple)) {
				$this->SyntaxError();
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function getLeftArrow(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->isLeftArrow($tuple)) {
				$this->SyntaxError();
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function getLeftBracket(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, '[');
		}

		protected function getLeftCurly(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, '{');
		}

		protected function getLeftParen(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, '(');
		}

		protected function getRightBracket(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, ']');
		}

		protected function getRightCurly(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, '}');
		}

		protected function getRightParen(VLD\Parser\Context $context) : VLD\Parser\Definition\Symbol {
			return $this->getSymbol($context, ')');
		}

		protected function getSymbol(VLD\Parser\Context $context, string $symbol) : VLD\Parser\Definition\Symbol {
			$tuple = $this->scanner->current();
			if (!$this->isSymbol($tuple, $symbol)) {
				$this->SyntaxError();
			}
			$symbol = new VLD\Parser\Definition\Symbol($context, (string) $tuple->token);
			$this->scanner->next();
			return $symbol;
		}

		protected function getTerminal(VLD\Parser\Context $context) : VLD\Parser\Definition\Terminal {
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

		protected function getArrayKey(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayKey {
			$tuple = $this->scanner->current();
			if (!$this->isArrayVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\ArrayKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getArrayTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			if ($this->isArrayVariable($this->scanner->current())) {
				return $this->getArrayVariable($context);
			}
			$terms = array();
			$this->getLeftBracket($context);
			if (!$this->isRightBracket($this->scanner->current())) {
				$terms[] = $this->getMixedTerm($context);
				while (!$this->isRightBracket($this->scanner->current())) {
					$this->getComma($context);
					$terms[] = $this->getMixedTerm($context);
				}
			}
			$this->getRightBracket($context);
			return new VLD\Parser\Definition\ArrayTerm($context, $terms);
		}

		protected function getArrayVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\ArrayVariable {
			$tuple = $this->scanner->current();
			if (!$this->isArrayVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\ArrayVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getBlockKey(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockKey {
			$tuple = $this->scanner->current();
			if (!$this->isBlockKey($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\BlockKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getBlockRef(VLD\Parser\Context $context) : VLD\Parser\Definition\Block {
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

		protected function getBlockTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Block {
			$tuple = $this->scanner->current();
			if ($this->isBlockRef($tuple)) {
				return $this->getBlockRef($context);
			}
			if ($this->isBlockVariable($tuple)) {
				return $this->getBlockVariable($context);
			}
			$this->getLeftCurly($context);
			$statements = array();
			while (!$this->isRightCurly($this->scanner->current())) {
				$statements[] = $this->getStatement($context);
			}
			$term = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->getRightCurly($context);
			return $term;
		}

		protected function getBlockVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\BlockVariable {
			$tuple = $this->scanner->current();
			if (!$this->isBlockVariable($tuple)) {
				$this->SyntaxError();
			}
			$variable = new VLD\Parser\Definition\BlockVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $variable;
		}

		protected function getBooleanKey(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanKey {
			$tuple = $this->scanner->current();
			if (!$this->isBooleanVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\BooleanKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getBooleanTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isBooleanVariable($tuple)) {
				return $this->getBooleanVariable($context);
			}
			$term = new VLD\Parser\Definition\BooleanTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getBooleanVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\BooleanVariable {
			$tuple = $this->scanner->current();
			if (!$this->isBooleanVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\BooleanVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getIntegerTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isIntegerTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\IntegerTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getMapKey(VLD\Parser\Context $context) : VLD\Parser\Definition\MapKey {
			$tuple = $this->scanner->current();
			if (!$this->isMapVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\MapKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getMapTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			if ($this->isMapVariable($this->scanner->current())) {
				return $this->getMapVariable($context);
			}
			$entries = array();
			$this->getLeftCurly($context);
			if (!$this->isRightCurly($this->scanner->current())) {
				$key = $this->getStringTerm($context);
				$this->getColon($context);
				$val = $this->getMixedTerm($context);
				$entries[] = Common\Tuple::box2($key, $val);
				while (!$this->isRightCurly($this->scanner->current())) {
					$this->getComma($context);
					$key = $this->getStringTerm($context);
					$this->getColon($context);
					$val = $this->getMixedTerm($context);
					$entries[] = Common\Tuple::box2($key, $val);
				}
			}
			$this->getRightCurly($context);
			return new VLD\Parser\Definition\MapTerm($context, $entries);
		}

		protected function getMapVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\MapVariable {
			$tuple = $this->scanner->current();
			if (!$this->isMapVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\MapVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getMixedKey(VLD\Parser\Context $context) : VLD\Parser\Definition\MixedKey {
			$tuple = $this->scanner->current();
			if (!$this->isMixedKey($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\MixedKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getMixedTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isArrayTerm($tuple)) {
				return $this->getArrayTerm($context);
			}
			if ($this->isBooleanTerm($tuple)) {
				return $this->getBooleanTerm($context);
			}
			if ($this->isMapTerm($tuple)) {
				return $this->getMapTerm($context);
			}
			if ($this->isNullTerm($tuple)) {
				return $this->getNullTerm($context);
			}
			if ($this->isNumberTerm($tuple)) {
				return $this->getNumberTerm($context);
			}
			if ($this->isStringTerm($tuple)) {
				return $this->getStringTerm($context);
			}
			return $this->getMixedVariable($context);
		}

		protected function getMixedVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\MixedVariable {
			$tuple = $this->scanner->current();
			if (!$this->isMixedVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\MixedVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getNullTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isNullTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\NullTerm($context);
			$this->scanner->next();
			return $term;
		}

		protected function getNumberKey(VLD\Parser\Context $context) : VLD\Parser\Definition\NumberKey {
			$tuple = $this->scanner->current();
			if (!$this->isNumberVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\NumberKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getNumberTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isIntegerTerm($tuple)) {
				return $this->getIntegerTerm($context);
			}
			if ($this->isRealTerm($tuple)) {
				return $this->getRealTerm($context);
			}
			return $this->getNumberVariable($context);
		}

		protected function getNumberVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\NumberVariable {
			$tuple = $this->scanner->current();
			if (!$this->isNumberVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\NumberVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getPathTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isLeftArrow($tuple)) {
				$this->getLeftArrow($context);
				if ($this->isUnderscore($this->scanner->current())) {
					$term = new VLD\Parser\Definition\PathTerm($context);
					$this->scanner->next();
					return $term;
				}
				return $this->getArrayTerm($context);
			}
			return new VLD\Parser\Definition\PathTerm($context);
		}

		protected function getRealTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if (!$this->isRealTerm($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\RealTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getStringKey(VLD\Parser\Context $context) : VLD\Parser\Definition\StringKey {
			$tuple = $this->scanner->current();
			if (!$this->isStringVariable($tuple)) {
				$this->SyntaxError();
			}
			$key = new VLD\Parser\Definition\StringKey($context, (string) $tuple->token);
			$this->scanner->next();
			return $key;
		}

		protected function getStringTerm(VLD\Parser\Context $context) : VLD\Parser\Definition\Term {
			$tuple = $this->scanner->current();
			if ($this->isStringVariable($tuple)) {
				return $this->getStringVariable($context);
			}
			$term = new VLD\Parser\Definition\StringTerm($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		protected function getStringVariable(VLD\Parser\Context $context) : VLD\Parser\Definition\StringVariable {
			$tuple = $this->scanner->current();
			if (!$this->isStringVariable($tuple)) {
				$this->SyntaxError();
			}
			$term = new VLD\Parser\Definition\StringVariable($context, (string) $tuple->token);
			$this->scanner->next();
			return $term;
		}

		#endregion

		#region Statements

		#region General

		protected function getStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\Statement {
			$tuple = $this->scanner->current();

			// Simple Statements
			if ($this->isKeyword($tuple, 'dump')) {
				return $this->getDumpStatement($context);
			}
			if ($this->isKeyword($tuple, 'eval')) {
				return $this->getEvalStatement($context);
			}
			if ($this->isKeyword($tuple, 'halt')) {
				return $this->getHaltStatement($context);
			}
			if ($this->isKeyword($tuple, 'install')) {
				return $this->getInstallStatement($context);
			}
			if ($this->isKeyword($tuple, 'set')) {
				return $this->getSetStatement($context);
			}

			// Complex Statements
			if ($this->isKeyword($tuple, 'is')) {
				return $this->getIsStatement($context);
			}
			if ($this->isKeyword($tuple, 'iterate')) {
				return $this->getIterateStatement($context);
			}
			if ($this->isKeyword($tuple, 'not')) {
				return $this->getNotStatement($context);
			}
			if ($this->isKeyword($tuple, 'on')) {
				return $this->getOnStatement($context);
			}
			if ($this->isKeyword($tuple, 'run')) {
				return $this->getRunStatement($context);
			}
			if ($this->isKeyword($tuple, 'select')) {
				return $this->getSelectStatement($context);
			}

			// Shortcut Statements
			if ($this->isLeftBracket($tuple)) {
				return $this->getAllStatement($context); // run("all") do {}.
			}
			if ($this->isLeftCurly($tuple)) {
				return $this->getSeqStatement($context); // run("seq") do {}.
			}
			if ($this->isLeftParen($tuple)) {
				return $this->getSelStatement($context); // run("sel") do {}.
			}

			// Error Handling
			$this->SyntaxError();
		}

		#endregion

		#region Simple Statements

		protected function getDumpStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\DumpStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$this->getRightParen($context);
			$args['paths'] = $this->getPathTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\DumpStatement($context, $args);
		}

		protected function getEvalStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\EvalStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$args['module'] = $this->getStringTerm($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$this->getComma($context);
				$args['policy'] = $this->getMixedTerm($context);
			}
			$this->getRightParen($context);
			$args['paths'] = $this->getPathTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\EvalStatement($context, $args);
		}

		protected function getHaltStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\HaltStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$this->getRightParen($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\HaltStatement($context, $args);
		}

		protected function getInstallStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\InstallStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$args['uri'] = $this->getStringTerm($context);
			$this->getRightParen($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\InstallStatement($context, $args);
		}

		protected function getSetStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SetStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$tuple = $this->scanner->current();
			if ($this->isArrayKey($tuple)) {
				$args['key'] = $this->getArrayKey($context);
				$this->getComma($context);
				$args['term'] = $this->getArrayTerm($context);
			}
			else if ($this->isBlockKey($tuple)) {
				$args['key'] = $this->getBlockKey($context);
				$this->getComma($context);
				$args['term'] = $this->getBlockTerm($context);
			}
			else if ($this->isBooleanKey($tuple)) {
				$args['key'] = $this->getBooleanKey($context);
				$this->getComma($context);
				$args['term'] = $this->getBooleanTerm($context);
			}
			else if ($this->isMapKey($tuple)) {
				$args['key'] = $this->getMapKey($context);
				$this->getComma($context);
				$args['term'] = $this->getMapTerm($context);
			}
			else if ($this->isMixedKey($tuple)) {
				$args['key'] = $this->getMixedKey($context);
				$this->getComma($context);
				$args['term'] = $this->getMixedTerm($context);
			}
			else if ($this->isNumberKey($tuple)) {
				$args['key'] = $this->getNumberKey($context);
				$this->getComma($context);
				$args['term'] = $this->getNumberTerm($context);
			}
			else if ($this->isStringKey($tuple)) {
				$args['key'] = $this->getStringKey($context);
				$this->getComma($context);
				$args['term'] = $this->getStringTerm($context);
			}
			else {
				$this->SyntaxError();
			}
			$this->getRightParen($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\SetStatement($context, $args);
		}

		#endregion

		#region Complex Statements

		protected function getIsStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IsStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$args['module'] = $this->getStringTerm($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$this->getComma($context);
				$args['policy'] = $this->getMixedTerm($context);
			}
			$this->getRightParen($context);
			$args['paths'] = $this->getPathTerm($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\IsStatement($context, $args);
		}

		protected function getIterateStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\IterateStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$args['control'] = $this->getStringTerm($context);
				if (!$this->isRightParen($this->scanner->current())) {
					$this->getComma($context);
					$args['policy'] = $this->getMixedTerm($context);
				}
			}
			$this->getRightParen($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\IterateStatement($context, $args);
		}

		protected function getNotStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\NotStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$args['module'] = $this->getStringTerm($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$this->getComma($context);
				$args['policy'] = $this->getMixedTerm($context);
			}
			$this->getRightParen($context);
			$args['paths'] = $this->getPathTerm($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\NotStatement($context, $args);
		}

		protected function getOnStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\OnStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			$args['event'] = $this->getStringTerm($context);
			$this->getRightParen($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\OnStatement($context, $args);
		}

		protected function getRunStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$args['control'] = $this->getStringTerm($context);
				if (!$this->isRightParen($this->scanner->current())) {
					$this->getComma($context);
					$args['policy'] = $this->getMixedTerm($context);
				}
			}
			$this->getRightParen($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\RunStatement($context, $args);
		}

		protected function getSelectStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\SelectStatement {
			$this->scanner->next();
			$args = array();
			$this->getLeftParen($context);
			if (!$this->isRightParen($this->scanner->current())) {
				$args['control'] = $this->getStringTerm($context);
				if (!$this->isRightParen($this->scanner->current())) {
					$this->getComma($context);
					$args['policy'] = $this->getMixedTerm($context);
				}
			}
			$this->getRightParen($context);
			$args['paths'] = $this->getPathTerm($context);
			$this->getDo($context);
			$args['block'] = $this->getBlockTerm($context);
			$this->getTerminal($context);
			return new VLD\Parser\Definition\SelectStatement($context, $args);
		}

		#endregion

		#region Shortcut Statements

		protected function getAllStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$tuple = $this->scanner->current();
			if (!$this->isLeftBracket($tuple)) {
				$this->SyntaxError();
			}
			$this->scanner->next();
			$args = array();
			$args['control'] = new VLD\Parser\Definition\StringTerm($context, '"all"');
			$statements = array();
			while (!$this->isRightBracket($this->scanner->current())) {
				$statements[] = $this->getStatement($context);
			}
			$args['block'] = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->getRightBracket($context);
			return new VLD\Parser\Definition\RunStatement($context, $args);
		}

		protected function getSelStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$tuple = $this->scanner->current();
			if (!$this->isLeftParen($tuple)) {
				$this->SyntaxError();
			}
			$this->scanner->next();
			$args = array();
			$args['control'] = new VLD\Parser\Definition\StringTerm($context, '"sel"');
			$statements = array();
			while (!$this->isRightParen($this->scanner->current())) {
				$statements[] = $this->getStatement($context);
			}
			$args['block'] = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->getRightParen($context);
			return new VLD\Parser\Definition\RunStatement($context, $args);
		}

		protected function getSeqStatement(VLD\Parser\Context $context) : VLD\Parser\Definition\RunStatement {
			$tuple = $this->scanner->current();
			if (!$this->isLeftCurly($tuple)) {
				$this->SyntaxError();
			}
			$this->scanner->next();
			$args = array();
			$args['control'] = new VLD\Parser\Definition\StringTerm($context, '"seq"');
			$statements = array();
			while (!$this->isRightCurly($this->scanner->current())) {
				$statements[] = $this->getStatement($context);
			}
			$args['block'] = new VLD\Parser\Definition\BlockTerm($context, $statements);
			$this->getRightCurly($context);
			return new VLD\Parser\Definition\RunStatement($context, $args);
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
			if (static::$config === null) {
				$directory = dirname(__FILE__);
				static::$config = Config\Inc\Reader::load(new IO\File($directory . '/Parser/Config.php'))->read();
			}
			$message = empty($variables) ? (string) $message : strtr((string) $message, $variables);
			$logs = static::$config['logs'];
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
			if (static::$config['throw']) {
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
			return $this->isLeftBracket($tuple) || $this->isArrayVariable($tuple);
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
			return $this->isLeftCurly($tuple) || $this->isBlockRef($tuple) || $this->isBlockVariable($tuple);
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

		protected function isDoSentinel(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isKeyword($tuple, 'do');
		}

		protected function isIntegerTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'NUMBER:INTEGER'));
		}

		protected function isKeyword(Lexer\Scanner\Tuple $tuple, string $identifier) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'KEYWORD') && ((string) $tuple->token === $identifier));
		}

		protected function isLeftArrow(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'ARROW:LEFT'));
		}

		protected function isLeftBracket(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '[');
		}

		protected function isLeftCurly(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '{');
		}

		protected function isLeftParen(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '(');
		}

		protected function isMapKey(Lexer\Scanner\Tuple $tuple) : bool {
			return (!is_null($tuple) && ((string) $tuple->type === 'VARIABLE:MAP'));
		}

		protected function isMapTerm(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isLeftCurly($tuple) || $this->isMapVariable($tuple);
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

		protected function isRightBracket(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, ']');
		}

		protected function isRightCurly(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '}');
		}

		protected function isRightParen(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, ')');
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

		protected function isUnderscore(Lexer\Scanner\Tuple $tuple) : bool {
			return $this->isSymbol($tuple, '_');
		}

		#endregion

	}

}