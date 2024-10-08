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

namespace Unicity\HTTP;

use Unicity\Core;
use Unicity\EVT;
use Unicity\HTTP;

class RequestBroker extends Core\AbstractObject
{
    /**
     * This variable stores a reference to the dispatcher.
     *
     * @access protected
     * @var EVT\IServer
     */
    protected $server;

    /**
     * This constructor initializes the class.
     *
     * @access public
     */
    public function __construct()
    {
        $this->server = new EVT\Server();
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->server);
    }

    /**
     * This method executes the given request.
     *
     * @access public
     * @return int the response status
     */
    public function execute($request)
    {
        return $this->executeSync($request)->status;
    }

    public function executeSync($request)
    {
        $http_code = 200;
        $initializedRequest = $this->initializeRequest($request);
        $resource = $initializedRequest['curl'];
        $headersLength = $initializedRequest['headersLength'];
        // Response headers MUST be set by reference due to CURLOPT_HEADERFUNCTION being called when curl_exec() (below) is called
        $responseHeaders = &$initializedRequest['responseHeaders'];

        $curlResponse = curl_exec($resource);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($resource, CURLINFO_HEADER_SIZE);
        $header = substr($curlResponse, 0, $header_size);
        $body = substr($curlResponse, $header_size);

        $response = null;

        if (curl_errno($resource)) {
            $error = curl_error($resource);
            @curl_close($resource);
            $status = 503;
            $response = HTTP\Response::factory([
                'body' => $error,
                'headers' => [
                    'http_code' => $status,
                ],
                'status' => $status,
                'statusText' => HTTP\Response::getStatusText($status),
                'url' => $request->url,
            ]);
            $this->server->publish('requestFailed', $response);
            $this->server->publish('requestCompleted', $response);
            $http_code = max($http_code, $status);
        } else {

            $body = substr($body, $headersLength);
            $headers = curl_getinfo($resource);
            $headers = array_merge($headers, $responseHeaders);

            @curl_close($resource);
            $status = $headers['http_code'];
            $response = HTTP\Response::factory([
                'body' => $body,
                'headers' => $headers,
                'status' => $status,
                'statusText' => HTTP\Response::getStatusText($status),
                'url' => $request->url,
            ]);
            if (($status >= 200) && ($status < 300)) {
                $this->server->publish('requestSucceeded', $response);
                $this->server->publish('requestCompleted', $response);
                $http_code = max($http_code, $status);
            } else {
                $this->server->publish('requestFailed', $response);
                $this->server->publish('requestCompleted', $response);
                $http_code = max($http_code, $status);
            }
        }

        return $response;
    }

    private function initializeRequest($request)
    {
        $this->server->publish('requestInitiated', $request);

        $resource = curl_init();

        curl_setopt($resource, CURLOPT_HEADER, false);
        if (isset($request->headers) && !empty($request->headers)) {
            $headers = [];
            foreach ($request->headers as $name => $value) {
                $headers[] = "{$name}: {$value}";
            }
            curl_setopt($resource, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($resource, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($resource, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($resource, CURLOPT_HEADER, true); // Include headers in the response
        $responseHeaders = [];
        $headersLength = 0;
        // this function is called by curl for each header received
        curl_setopt(
            $resource,
            CURLOPT_HEADERFUNCTION,
            function ($resource, $header) use (&$responseHeaders, &$headersLength) {
                $len = strlen($header);
                $headersLength += $len;
                $header = explode(':', $header, 2);
                if (count($header) < 2) { // ignore invalid headers
                    return $len;
                }

                $responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );
        curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($resource, CURLOPT_TIMEOUT, 120);
        curl_setopt($resource, CURLOPT_URL, $request->url);
        if (preg_match('/^https/', $request->url)) {
            curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $method = strtoupper($request->method);
        switch ($method) {
            case 'GET':
                // do nothing
                break;
            case 'POST':
                curl_setopt($resource, CURLOPT_POST, 1);
                if (isset($request->body)) {
                    $body = $request->body;
                    if (is_array($body)) {
                        $body = http_build_query($body);
                    }
                    curl_setopt($resource, CURLOPT_POSTFIELDS, $body);
                }

                break;
            default:
                curl_setopt($resource, CURLOPT_CUSTOMREQUEST, $method);
                if (isset($request->body)) {
                    $body = $request->body;
                    if (is_array($body)) {
                        $body = http_build_query($body);
                    }
                    curl_setopt($resource, CURLOPT_POSTFIELDS, $body);
                }

                break;
        }

        if (isset($request->options) && !empty($request->options)) {
            foreach ($request->options as $name => $value) {
                curl_setopt($resource, $name, $value);
            }
        }

        if (isset($request->credentials) && !empty($request->credentials)) {
            curl_setopt($resource, CURLOPT_HTTPAUTH, $request->credentials['method'] ?? CURLAUTH_ANY);
            if (isset($request->credentials['username']) && isset($request->credentials['password'])) {
                curl_setopt($resource, CURLOPT_USERPWD, sprintf('%s:%s', $request->credentials['username'], $request->credentials['password']));
            }
        }

        return [
            'curl' => $resource,
            'headersLength' => $headersLength,
            // Response headers intentionally returned by reference due to CURLOPT_HEADERFUNCTION being called when the curl is executed
            'responseHeaders' => &$responseHeaders,
        ];
    }

    /**
     * This method executes the given requests.
     *
     * @access public
     * @param array $requests the requests to be sent
     * @return int the response status
     */
    public function executeAll(array $requests)
    {
        $this->server->publish('requestOpened');

        $http_code = 200;

        $dispatcher = curl_multi_init();
        $resources = [];
        $count = count($requests);
        $responseHeaders = null;

        for ($i = 0; $i < $count; $i++) {
            $request = $requests[$i];

            $initializedRequest = $this->initializeRequest($request);
            $resource = $initializedRequest['curl'];
            $headersLength = $initializedRequest['headersLength'];
            $responseHeaders = $initializedRequest['responseHeaders'];

            $resources[$i] = curl_copy_handle($resource);
            curl_multi_add_handle($dispatcher, $resources[$i]);
        }

        $running = null;
        do {
            curl_multi_exec($dispatcher, $running);
        } while ($running);

        for ($i = 0; $i < $count; $i++) {
            $resource = $resources[$i];
            $request = $requests[$i];

            curl_multi_remove_handle($dispatcher, $resource);
            $body = curl_multi_getcontent($resource);
            if (curl_errno($resource)) {
                $error = curl_error($resource);
                @curl_close($resource);
                $status = 503;
                $response = HTTP\Response::factory([
                    'body' => $error,
                    'headers' => [
                        'http_code' => $status,
                    ],
                    'status' => $status,
                    'statusText' => HTTP\Response::getStatusText($status),
                    'url' => $request->url,
                ]);
                $this->server->publish('requestFailed', $response);
                $this->server->publish('requestCompleted', $response);
                $http_code = max($http_code, $status);
            } else {

                $body = substr($body, $headersLength);
                $headers = curl_getinfo($resource);
                $headers = array_merge($headers, $responseHeaders);

                @curl_close($resource);
                $status = $headers['http_code'];
                $response = HTTP\Response::factory([
                    'body' => $body,
                    'headers' => $headers,
                    'status' => $status,
                    'statusText' => HTTP\Response::getStatusText($status),
                    'url' => $request->url,
                ]);
                if (($status >= 200) && ($status < 300)) {
                    $this->server->publish('requestSucceeded', $response);
                    $this->server->publish('requestCompleted', $response);
                    $http_code = max($http_code, $status);
                } else {
                    $this->server->publish('requestFailed', $response);
                    $this->server->publish('requestCompleted', $response);
                    $http_code = max($http_code, $status);
                }
            }
        }
        curl_multi_close($dispatcher);

        $this->server->publish('responseReceived', $http_code);

        return $http_code;
    }

    /**
     * This method adds a closing handler.
     *
     * @access public
     * @param callable $handler the closing handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onClosing(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('responseReceived', $handler);

        return $this;
    }

    /**
     * This method adds a completion handler.
     *
     * @access public
     * @param callable $handler the completion handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onCompletion(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('requestCompleted', $handler);

        return $this;
    }

    /**
     * This method adds a failure handler.
     *
     * @access public
     * @param callable $handler the failure handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onFailure(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('requestFailed', $handler);

        return $this;
    }

    /**
     * This method adds an initialization handler.
     *
     * @access public
     * @param callable $handler the initialization handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onInitiation(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('requestInitiated', $handler);

        return $this;
    }

    /**
     * This method adds an opening handler.
     *
     * @access public
     * @param callable $handler the opening handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onOpening(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('requestOpened', $handler);

        return $this;
    }

    /**
     * This method adds a success handler.
     *
     * @access public
     * @param callable $handler the success handler to be added
     * @return HTTP\RequestBroker a reference to this class
     */
    public function onSuccess(callable $handler): HTTP\RequestBroker
    {
        $this->server->subscribe('requestSucceeded', $handler);

        return $this;
    }

}
