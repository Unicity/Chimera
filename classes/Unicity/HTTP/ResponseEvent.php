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

namespace Unicity\HTTP {

	use \Unicity\EVT;

	class ResponseEvent extends EVT\Event {

		protected $body;
		protected $headers;
		protected $status;
		protected $url;

		public function __construct(EVT\Source $source, string $url, string $body, array $headers = []) {
			parent::__construct($source);
			$this->body = $body;
			$this->headers = $headers;
			$this->status = $headers['http_code'] ?? 500;
			$this->url = $url;
		}

		public function __destruct() {
			parent::__destruct();
			unset($this->body);
			unset($this->headers);
			unset($this->status);
			unset($this->url);
		}

		public function getBody() : string {
			return $this->body;
		}

		public function getHeaders() : array {
			return $this->headers;
		}

		public function getStatus() : int {
			return (int) $this->status;
		}

		public function getURL() : string {
			return $this->url;
		}

		public function jsonSerialize() {
			$serialized = parent::jsonSerialize();
			$serialized['body'] = $this->body;
			$serialized['headers'] = $this->headers;
			$serialized['status'] = $this->status;
			$serialized['url'] = $this->url;
			return $serialized;
		}

	}

}
