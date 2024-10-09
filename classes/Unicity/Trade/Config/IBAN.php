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

return [
    'AD' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'AE' => ['length' => 23, 'format' => function ($iban) { return []; }, ],
    'AL' => [
        'format' => function ($iban) {
            return [
                'k' => substr($iban, 2, 2),
                'b' => substr($iban, 4, 3),
                's' => substr($iban, 7, 4),
                'x' => substr($iban, 11, 1),
                'c' => substr($iban, 12, 16),
            ];
        },
        'length' => 28,
    ],
    'AO' => ['length' => 25, 'format' => function ($iban) { return []; }, ],
    'AT' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'AZ' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'BA' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'BE' => ['length' => 16, 'format' => function ($iban) { return []; }, ],
    'BF' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'BG' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'BH' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'BI' => ['length' => 16, 'format' => function ($iban) { return []; }, ],
    'BJ' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'BR' => ['length' => 29, 'format' => function ($iban) { return []; }, ],
    'CG' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'CH' => ['length' => 21, 'format' => function ($iban) { return []; }, ],
    'CI' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'CM' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'CR' => ['length' => 21, 'format' => function ($iban) { return []; }, ],
    'CV' => ['length' => 25, 'format' => function ($iban) { return []; }, ],
    'CY' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'CZ' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'DE' => [
        'format' => function ($iban) {
            return [
                'k' => substr($iban, 2, 2),
                'b' => substr($iban, 4, 8),
                'c' => substr($iban, 12, 10),
            ];
        },
        'length' => 22,
    ],
    'DK' => ['length' => 18, 'format' => function ($iban) { return []; }, ],
    'DO' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'DZ' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'EE' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'EG' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'ES' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'FI' => ['length' => 18, 'format' => function ($iban) { return []; }, ],
    'FO' => ['length' => 18, 'format' => function ($iban) { return []; }, ],
    'FR' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'GA' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'GB' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'GE' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'GI' => ['length' => 23, 'format' => function ($iban) { return []; }, ],
    'GL' => ['length' => 18, 'format' => function ($iban) { return []; }, ],
    'GR' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'GT' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'HR' => ['length' => 21, 'format' => function ($iban) { return []; }, ],
    'HU' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'IE' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'IL' => ['length' => 23, 'format' => function ($iban) { return []; }, ],
    'IR' => ['length' => 26, 'format' => function ($iban) { return []; }, ],
    'IS' => ['length' => 26, 'format' => function ($iban) { return []; }, ],
    'IT' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'JO' => ['length' => 30, 'format' => function ($iban) { return []; }, ],
    'KW' => ['length' => 30, 'format' => function ($iban) { return []; }, ],
    'KZ' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'LB' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'LI' => ['length' => 21, 'format' => function ($iban) { return []; }, ],
    'LT' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'LU' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
    'LV' => ['length' => 21, 'format' => function ($iban) { return []; }, ],
    'MC' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'MD' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'ME' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'MG' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'MK' => ['length' => 19, 'format' => function ($iban) { return []; }, ],
    'ML' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'MR' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'MT' => ['length' => 31, 'format' => function ($iban) { return []; }, ],
    'MU' => ['length' => 30, 'format' => function ($iban) { return []; }, ],
    'MZ' => ['length' => 25, 'format' => function ($iban) { return []; }, ],
    'NL' => ['length' => 18, 'format' => function ($iban) { return []; }, ],
    'NO' => ['length' => 15, 'format' => function ($iban) { return []; }, ],
    'PK' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'PL' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'PS' => ['length' => 29, 'format' => function ($iban) { return []; }, ],
    'PT' => ['length' => 25, 'format' => function ($iban) { return []; }, ],
    'QA' => ['length' => 29, 'format' => function ($iban) { return []; }, ],
    'RO' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'RS' => ['length' => 22, 'format' => function ($iban) { return []; }, ],
    'SA' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'SE' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'SI' => ['length' => 19, 'format' => function ($iban) { return []; }, ],
    'SK' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'SM' => ['length' => 27, 'format' => function ($iban) { return []; }, ],
    'SN' => ['length' => 28, 'format' => function ($iban) { return []; }, ],
    'TL' => ['length' => 23, 'format' => function ($iban) { return []; }, ],
    'TN' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'TR' => ['length' => 26, 'format' => function ($iban) { return []; }, ],
    'UA' => ['length' => 29, 'format' => function ($iban) { return []; }, ],
    'VG' => ['length' => 24, 'format' => function ($iban) { return []; }, ],
    'XK' => ['length' => 20, 'format' => function ($iban) { return []; }, ],
];
