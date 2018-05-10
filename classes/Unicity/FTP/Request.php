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

namespace Unicity\FTP {

	use \Unicity\EVT;

	class Request extends EVT\Response {

		public function __construct(array $map = []) {
			parent::__construct(array_merge($map, [
				'body' => '',
				'host' => '',
				'local_uri' => null,
				'method' => 'PUT', // Note: 'GET' to download; 'PUT' to upload (default)
				'mode' => FTP_ASCII,
				'port' => 21,
				'remote_uri' => null,
			]));
		}

	}

}