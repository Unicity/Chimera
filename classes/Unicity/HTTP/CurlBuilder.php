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
			$data = $this->request->data ?? "";
			if (is_object($data) || is_array($data)) {
				$data = $this->pretty ? json_encode($data, JSON_PRETTY_PRINT) : json_encode($data);
			}
			return "--data-raw '${data}'";
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
			return trim($this->url ?? '');
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
