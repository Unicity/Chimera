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

		private function getFlags() : array {
			$options = (array) $this->request->options ?? [];
			$flags = [];
			if (isset($options[CURLOPT_CONNECTTIMEOUT])) {
				$flags[] = "--connect-timeout {$options[CURLOPT_CONNECTTIMEOUT]}";
			}
			if (isset($options[CURLOPT_HTTP_VERSION])) {
				$flags[] = "--version {$options[CURLOPT_HTTP_VERSION]}";
			}
			if (isset($options[CURLOPT_REFERER])) {
				$flags[] = "--referer {$options[CURLOPT_REFERER]}";
			}
			if (isset($options[CURLOPT_TIMEOUT])) {
				$flags[] = "--max-time {$options[CURLOPT_TIMEOUT]}";
			}
			return $flags;
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

		public function toCurl($detach = false) : string {
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
			$flags = $this->getFlags();
			if (!empty($flags)) {
				$curl .= $delimiter;
				$curl .= implode($delimiter, $flags);
			}
			if ($detach) {
				$curl .= '> /dev/null 2>&1 &';
			}
			return $curl;
		}

		public static function factory(EVT\Request $request, bool $pretty = false) : CurlBuilder {
			return new CurlBuilder($request, $pretty);
		}

		public static function stringify(EVT\Request $request, bool $pretty = false, bool $detach = false) : string {
			return CurlBuilder::factory($request, $pretty)->toCurl($detach);
		}

	}

}
