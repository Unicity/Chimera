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

return array(
	'AD' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'AE' => array('length' => 23, 'format' => function($iban) { return array(); },),
	'AL' => array(
		'format' => function($iban) {
			return array(
				'k' => substr($iban, 2, 2),
				'b' => substr($iban, 4, 3),
				's' => substr($iban, 7, 4),
				'x' => substr($iban, 11, 1),
				'c' => substr($iban, 12, 16),
			);
		},
		'length' => 28,
	),
	'AO' => array('length' => 25, 'format' => function($iban) { return array(); },),
	'AT' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'AZ' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'BA' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'BE' => array('length' => 16, 'format' => function($iban) { return array(); },),
	'BF' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'BG' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'BH' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'BI' => array('length' => 16, 'format' => function($iban) { return array(); },),
	'BJ' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'BR' => array('length' => 29, 'format' => function($iban) { return array(); },),
	'CG' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'CH' => array('length' => 21, 'format' => function($iban) { return array(); },),
	'CI' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'CM' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'CR' => array('length' => 21, 'format' => function($iban) { return array(); },),
	'CV' => array('length' => 25, 'format' => function($iban) { return array(); },),
	'CY' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'CZ' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'DE' => array(
		'format' => function($iban) {
			return array(
				'k' => substr($iban, 2, 2),
				'b' => substr($iban, 4, 8),
				'c' => substr($iban, 12, 10),
			);
		},
		'length' => 22,
	),
	'DK' => array('length' => 18, 'format' => function($iban) { return array(); },),
	'DO' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'DZ' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'EE' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'EG' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'ES' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'FI' => array('length' => 18, 'format' => function($iban) { return array(); },),
	'FO' => array('length' => 18, 'format' => function($iban) { return array(); },),
	'FR' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'GA' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'GB' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'GE' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'GI' => array('length' => 23, 'format' => function($iban) { return array(); },),
	'GL' => array('length' => 18, 'format' => function($iban) { return array(); },),
	'GR' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'GT' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'HR' => array('length' => 21, 'format' => function($iban) { return array(); },),
	'HU' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'IE' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'IL' => array('length' => 23, 'format' => function($iban) { return array(); },),
	'IR' => array('length' => 26, 'format' => function($iban) { return array(); },),
	'IS' => array('length' => 26, 'format' => function($iban) { return array(); },),
	'IT' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'JO' => array('length' => 30, 'format' => function($iban) { return array(); },),
	'KW' => array('length' => 30, 'format' => function($iban) { return array(); },),
	'KZ' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'LB' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'LI' => array('length' => 21, 'format' => function($iban) { return array(); },),
	'LT' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'LU' => array('length' => 20, 'format' => function($iban) { return array(); },),
	'LV' => array('length' => 21, 'format' => function($iban) { return array(); },),
	'MC' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'MD' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'ME' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'MG' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'MK' => array('length' => 19, 'format' => function($iban) { return array(); },),
	'ML' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'MR' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'MT' => array('length' => 31, 'format' => function($iban) { return array(); },),
	'MU' => array('length' => 30, 'format' => function($iban) { return array(); },),
	'MZ' => array('length' => 25, 'format' => function($iban) { return array(); },),
	'NL' => array('length' => 18, 'format' => function($iban) { return array(); },),
	'NO' => array('length' => 15, 'format' => function($iban) { return array(); },),
	'PK' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'PL' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'PS' => array('length' => 29, 'format' => function($iban) { return array(); },),
	'PT' => array('length' => 25, 'format' => function($iban) { return array(); },),
	'QA' => array('length' => 29, 'format' => function($iban) { return array(); },),
	'RO' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'RS' => array('length' => 22, 'format' => function($iban) { return array(); },),
	'SA' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'SE' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'SI' => array('length' => 19, 'format' => function($iban) { return array(); },),
	'SK' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'SM' => array('length' => 27, 'format' => function($iban) { return array(); },),
	'SN' => array('length' => 28, 'format' => function($iban) { return array(); },),
	'TL' => array('length' => 23, 'format' => function($iban) { return array(); },),
	'TN' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'TR' => array('length' => 26, 'format' => function($iban) { return array(); },),
	'UA' => array('length' => 29, 'format' => function($iban) { return array(); },),
	'VG' => array('length' => 24, 'format' => function($iban) { return array(); },),
	'XK' => array('length' => 20, 'format' => function($iban) { return array(); },),
);