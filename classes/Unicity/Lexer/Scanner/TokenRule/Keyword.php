<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2013 Spadefoot Team
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

declare(strict_types=1);

namespace Unicity\Lexer\Scanner\TokenRule;

use Unicity\Common;
use Unicity\Core;
use Unicity\IO;
use Unicity\Lexer;

/**
 * This class represents the rule definition for a "keyword" token, which the tokenizer will use
 * to tokenize a string.
 *
 * @access public
 * @class
 * @package Lexer
 */
class Keyword extends Core\AbstractObject implements Lexer\Scanner\ITokenRule
{
    /**
     * This variable stores a list of reserved keywords.
     *
     * @access protected
     * @var Common\HashSet
     */
    protected $keywords;

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param mixed $keywords a list of reserved keywords
     */
    public function __construct($keywords = null)
    {
        $this->keywords = ($keywords instanceof Common\HashSet) ? $keywords : new Common\HashSet($keywords);
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->keywords);
    }

    /**
     * This method return a tuple representing the token discovered.
     *
     * @access public
     * @param \Unicity\IO\Reader $reader the reader to be used
     * @return \Unicity\Lexer\Scanner\Tuple a tuple representing the token
     *                                      discovered
     */
    public function process(IO\Reader $reader): ?Lexer\Scanner\Tuple
    {
        $index = $reader->position();
        $char = $reader->readChar($index, false);
        if (($char !== null) && preg_match('/^[_a-z]$/i', $char)) {
            $lookahead = $index;
            do {
                $lookahead++;
                $next = $reader->readChar($lookahead, false);
            } while (($next !== null) && preg_match('/^[_a-z0-9]$/i', $next));
            $token = $reader->readRange($index, $lookahead);
            $type = $this->keywords->hasValue($token)
                ? Lexer\Scanner\TokenType::keyword()
                : Lexer\Scanner\TokenType::identifier();
            $tuple = new Lexer\Scanner\Tuple($type, new Common\StringRef($token), $index);

            return $tuple;
        }

        return null;
    }

}
