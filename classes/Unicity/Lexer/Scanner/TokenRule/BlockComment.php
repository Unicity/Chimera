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
 * This class represents the rule definition for a "whitespace" token (i.e. C-style comment), which
 * the tokenizer will use to tokenize a string.
 *
 * @access public
 * @class
 * @package Lexer
 */
class BlockComment extends Core\AbstractObject implements Lexer\Scanner\ITokenRule
{
    /**
     * This variable stores the opening 2-character sequence.
     *
     * @access protected
     * @var string
     */
    protected $opening;

    /**
     * This variable stores the closing 2-character sequence.
     *
     * @access protected
     * @var string
     */
    protected $closing;

    /**
     * This constructor initializes the class.
     *
     * @access public
     * @param string $opening the opening 2-character sequence
     * @param string $closing the closing 2-character sequence
     */
    public function __construct(string $opening, string $closing)
    {
        $this->opening = $opening;
        $this->closing = $closing;
    }

    /**
     * This method releases any internal references to an object.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->opening);
        unset($this->closing);
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
        if ($char == $this->opening[0]) {
            $lookahead = $index + 1;
            $next = $reader->readChar($lookahead, false);
            if ($next == $this->opening[1]) {
                $lookahead += 2;
                while (!(($reader->readChar($lookahead - 1, false) == $this->closing[0]) && ($reader->readChar($lookahead, false) == $this->closing[1]))) {
                    $lookahead++;
                }
                $lookahead++;
                $token = $reader->readRange($index, $lookahead);
                $tuple = new Lexer\Scanner\Tuple(Lexer\Scanner\TokenType::whitespace(), new Common\StringRef($token), $index);

                return $tuple;
            }

        }

        return null;
    }

}
