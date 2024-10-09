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

declare(strict_types=1);

namespace Unicity\UnitTest;

/**
 * This class represents the parent of a test case.
 *
 * @access public
 * @class
 * @package UnitTest
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * This method asserts that a variable is one of the given types.
     *
     * @param array $expected the expected set of data types
     * @param mixed $actual the variable to be tested
     * @param string $message the message reported when the assertion
     *                        fails
     */
    public function assertInternalTypes(array $expected, $actual, $message = '') // TODO handle class inheritence and interfaces, as well as object
    {
        $type = gettype($actual);
        if ($type == 'object') {
            $type = trim(get_class($actual), '\\');
        }
        $this->assertTrue(in_array($type, $expected), $message);
    }

}
