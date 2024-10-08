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

namespace Unicity\VLD\Scanner\TokenRule;

use Unicity\Common;
use Unicity\Core;
use Unicity\IO;
use Unicity\Lexer;
use Unicity\VLD;

/**
 * This class represents the rule definition for a "number variable" token, which the
 * tokenizer will use to tokenize a string.
 *
 * @access public
 * @class
 * @package VLD
 */
class NumberVariable extends Core\AbstractObject implements Lexer\Scanner\ITokenRule
{
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
        if (($char !== null) && ($char === '#')) {
            $lookahead = $index + 1;
            $next = $reader->readChar($lookahead, false);
            if (($next !== null) && preg_match('/^[_a-z0-9]$/i', $next)) {
                do {
                    $lookahead++;
                    $next = $reader->readChar($lookahead, false);
                } while (($next !== null) && preg_match('/^[_a-z0-9]$/i', $next));
                $token = $reader->readRange($index, $lookahead);
                $tuple = new Lexer\Scanner\Tuple(VLD\Scanner\TokenType::variable_number(), new Common\StringRef($token), $index);

                return $tuple;
            }
        }

        return null;
    }

}
