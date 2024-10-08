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

namespace Unicity\VLD;

use Unicity\Core;

abstract class Code extends Core\AbstractObject
{
    // testing "presents"
    public const UNKNOWN = '0';
    public const VALUE_IS_REQUIRED = '1000';

    // testing "type"
    public const VALUE_IS_EQ_TYPE = '1100';
    public const VALUE_IS_RETYPABLE = '1101';

    // testing "value"
    public const VALUE_IS_EQ_VALUE = '2000';
    public const VALUE_IS_EQ_FIELD = '2001';
    public const VALUE_IS_EQ_ENUM = '2002';
    public const VALUE_IS_EQ_PATTERN = '2003';
    public const VALUE_IS_EQ_REGEX = '2004';
    public const VALUE_IS_LOWERCASE = '2005';
    public const VALUE_IS_UPPERCASE = '2006';
    public const VALUE_IS_UNDEFINED = '2007';
    public const VALUE_IS_NULL = '2008';
    public const VALUE_IS_UNSET = '2009';
    public const VALUE_NOT_FOUND = '2010';

    // testing "property"
    public const VALUE_IS_EQ_LENGTH = '2100';
    public const VALUE_IS_EQ_SIZE = '2101';
    public const VALUE_IS_DIVISIBLE_BY_VALUE = '2102';

    // testing "value"
    public const VALUE_IS_NE_VALUE = '3000';
    public const VALUE_IS_NE_FIELD = '3001';

    // testing "property"
    public const VALUE_IS_NE_LENGTH = '3100';
    public const VALUE_IS_NE_SIZE = '3101';

    // testing "value"
    public const VALUE_IS_GT_VALUE = '4000';

    // testing "property"
    public const VALUE_IS_GT_LENGTH = '4100';
    public const VALUE_IS_GT_SIZE = '4101';

    // testing "value"
    public const VALUE_IS_GE_VALUE = '5000';

    // testing "property"
    public const VALUE_IS_GE_LENGTH = '5100';
    public const VALUE_IS_GE_SIZE = '5101';

    // testing "value"
    public const VALUE_IS_LT_VALUE = '6000';

    // testing "property"
    public const VALUE_IS_LT_LENGTH = '6100';
    public const VALUE_IS_LT_SIZE = '6101';

    // testing "value"
    public const VALUE_IS_LE_VALUE = '7000';

    // testing "property"
    public const VALUE_IS_LE_LENGTH = '7100';
    public const VALUE_IS_LE_SIZE = '7101';

}
