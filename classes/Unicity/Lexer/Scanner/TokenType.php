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

namespace Unicity\Lexer\Scanner;

use Unicity\Core;
use Unicity\Lexer;

/**
 * This class enumerates the different types of tokens used by the tokenizer.
 *
 * @access public
 * @class
 * @final
 * @package Lexer
 */
final class TokenType extends Core\Enum implements Lexer\Scanner\ITokenType
{
    /**
     * This variable stores the enumerations.
     *
     * @access protected
     * @static
     * @var array an indexed array of the enumerations
     */
    protected static $__enums;

    /**
     * This method returns the token at the specified ordinal index.
     *
     * @access protected
     * @static
     * @param integer $ordinal the ordinal index of the token
     * @return Lexer\Scanner\ITokenType the token
     */
    protected static function __enum(int $ordinal): Lexer\Scanner\ITokenType
    {
        if (!is_array(static::$__enums)) {
            static::$__enums = [];
            static::$__enums[] = new static('delimiter', 'DELIMITER');
            static::$__enums[] = new static('error', 'ERROR');
            static::$__enums[] = new static('hexadecimal', 'HEXADECIMAL');
            static::$__enums[] = new static('identifier', 'IDENTIFIER');
            static::$__enums[] = new static('integer', 'NUMBER:INTEGER');
            static::$__enums[] = new static('keyword', 'KEYWORD');
            static::$__enums[] = new static('literal', 'LITERAL');
            static::$__enums[] = new static('operator', 'OPERATOR');
            static::$__enums[] = new static('parameter', 'PARAMETER');
            static::$__enums[] = new static('real', 'NUMBER:REAL');
            static::$__enums[] = new static('symbol', 'SYMBOL');
            static::$__enums[] = new static('terminal', 'TERMINAL');
            static::$__enums[] = new static('unknown', 'UNKNOWN');
            static::$__enums[] = new static('variable', 'VARIABLE');
            static::$__enums[] = new static('whitespace', 'WHITESPACE');
        }

        return static::$__enums[$ordinal];
    }

    /**
     * This constructor intiializes the enumeration with the specified properties.
     *
     * @access protected
     * @param string $name the name of the enumeration
     * @param mixed $value the value to be assigned to the enumeration
     */
    protected function __construct(string $name, $value)
    {
        $this->__name = $name;
        $this->__value = $value;
        $this->__ordinal = count(static::$__enums);
    }

    /**
     * This method returns the "delimiter" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function delimiter(): Lexer\Scanner\ITokenType
    {
        return static::__enum(0);
    }

    /**
     * This method returns the "error" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function error(): Lexer\Scanner\ITokenType
    {
        return static::__enum(1);
    }

    /**
     * This method returns the "hexadecimal" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function hexadecimal(): Lexer\Scanner\ITokenType
    {
        return static::__enum(2);
    }

    /**
     * This method returns the "identifier" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function identifier(): Lexer\Scanner\ITokenType
    {
        return static::__enum(3);
    }

    /**
     * This method returns the "integer" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function integer(): Lexer\Scanner\ITokenType
    {
        return static::__enum(4);
    }

    /**
     * This method returns the "keyword" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function keyword(): Lexer\Scanner\ITokenType
    {
        return static::__enum(5);
    }

    /**
     * This method returns the "literal" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function literal(): Lexer\Scanner\ITokenType
    {
        return static::__enum(6);
    }

    /**
     * This method returns the "operator" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function operator(): Lexer\Scanner\ITokenType
    {
        return static::__enum(7);
    }

    /**
     * This method returns the "parameter" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function parameter(): Lexer\Scanner\ITokenType
    {
        return static::__enum(8);
    }

    /**
     * This method returns the "real" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function real(): Lexer\Scanner\ITokenType
    {
        return static::__enum(9);
    }

    /**
     * This method returns the "symbol" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function symbol(): Lexer\Scanner\ITokenType
    {
        return static::__enum(10);
    }

    /**
     * This method returns the "terminal" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function terminal(): Lexer\Scanner\ITokenType
    {
        return static::__enum(11);
    }

    /**
     * This method returns the "unknown" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function unknown(): Lexer\Scanner\ITokenType
    {
        return static::__enum(12);
    }

    /**
     * This method returns the "variable" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function variable(): Lexer\Scanner\ITokenType
    {
        return static::__enum(13);
    }

    /**
     * This method returns the "whitespace" token.
     *
     * @access public
     * @static
     * @return Lexer\Scanner\ITokenType the token type
     */
    public static function whitespace(): Lexer\Scanner\ITokenType
    {
        return static::__enum(14);
    }

}
