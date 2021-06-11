<?php

declare(strict_types = 1);

namespace Unicity\HTTP {

	use \Unicity\EVT;

	class CurlBuilder {

		private $request;
		private $pretty;

		private function __construct(EVT\Request $request, bool $pretty) {
			$this->request = $request;
			$this->pretty = $pretty;
		}

		private function getBody() : string {
			$body = $this->request->body ?? "";
			if (is_object($body) || is_array($body)) {
				$body = $this->pretty ? json_encode($body, JSON_PRETTY_PRINT) : json_encode($body);
			}
			return "--data-raw '${body}'";
		}

		private function getDelimiter() : string {
			return $this->pretty ? "\n" : " ";
		}

		private function getHeaders() : array {
			$headers = (array) $this->request->headers ?? [];
			$keys = array_keys($headers);
			return array_map(fn($key) => "--header \"{$key}: {$headers[$key]}\"", $keys);
		}

		private function getMethod() : string {
			$method = $this->request->method ?? 'GET';
			$method = trim(strtoupper($method));
			return "--request {$method}";
		}

		private function getUrl() : string {
			return trim($this->request->url ?? '');
		}

		public function toCurl() : string {
			$delimiter = $this->getDelimiter();
			$curl = "curl --location {$this->getMethod()} '{$this->getUrl()}'";
			$headers = $this->getHeaders();
			if (!empty($headers)) {
				$curl .= $delimiter;
				$curl .= implode($delimiter, $headers);
			}
			if (isset($this->request->body)) {
				$curl .= $delimiter;
				$curl .= $this->getBody();
			}
			return $curl;
		}

		public static function stringify(EVT\Request $request, bool $pretty = false) : string {
			$curl = new CurlBuilder($request, $pretty);
			return $curl->toCurl();
		}

	}

}
