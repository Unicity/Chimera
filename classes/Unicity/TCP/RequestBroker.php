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

namespace Unicity\TCP;

use Unicity\Core;
use Unicity\EVT;
use Unicity\HTTP;
use Unicity\TCP;

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
     * @param TCP\Request $request the request to be sent
     * @return int the response status
     */
    public function execute(TCP\Request $request): int
    {
        return $this->executeAll([$request]);
    }

    /**
     * This method executes the given requests.
     *
     * @access public
     * @param array $requests the requests to be sent
     * @return int the response status
     */
    public function executeAll(array $requests): int
    {
        $this->server->publish('requestOpened');

        $http_code = 200;

        $count = count($requests);

        for ($i = 0; $i < $count; $i++) {
            $request = $requests[$i];

            $this->server->publish('requestInitiated', $request);

            $resource = fsockopen($request->host, intval($request->port), $errno, $errstr);
            if (is_resource($resource)) {
                if (isset($request->headers) && !empty($request->headers)) {
                    foreach ($request->headers as $name => $value) {
                        fwrite($resource, $name . ': ' . trim($value) . "\r\n");
                    }
                    fwrite($resource, "\r\n");
                }
                fwrite($resource, $request->body);
                fwrite($resource, "\r\n");
                socket_set_timeout($resource, 60); // TODO make configurable
                $body = '';
                while (!feof($resource)) {
                    $body .= fgets($resource, 4096);
                }
                @fclose($resource);
                $status = 200;
                $response = TCP\Response::factory([
                    'body' => $body,
                    'headers' => [
                        'http_code' => $status,
                    ],
                    'host' => $request->host,
                    'port' => $request->port,
                    'status' => $status,
                    'statusText' => HTTP\Response::getStatusText($status),
                ]);
                $this->server->publish('requestSucceeded', $response);
                $this->server->publish('requestCompleted', $response);
                $http_code = max($http_code, $status);
            } else {
                $status = 503;
                $response = TCP\Response::factory([
                    'body' => $errstr,
                    'headers' => [
                        'error_code' => $errno,
                        'http_code' => $status,
                    ],
                    'host' => $request->host,
                    'port' => $request->port,
                    'status' => $status,
                    'statusText' => HTTP\Response::getStatusText($status),
                ]);
                $this->server->publish('requestFailed', $response);
                $this->server->publish('requestCompleted', $response);
                $this->server->publish('responseReceived', $http_code);
                $http_code = max($http_code, $status);
            }
        }

        $this->server->publish('responseReceived', $http_code);

        return $http_code;
    }

    /**
     * This method adds a closing handler.
     *
     * @access public
     * @param callable $handler the closing handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onClosing(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('responseReceived', $handler);

        return $this;
    }

    /**
     * This method adds a completion handler.
     *
     * @access public
     * @param callable $handler the completion handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onCompletion(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('requestCompleted', $handler);

        return $this;
    }

    /**
     * This method adds an initialization handler.
     *
     * @access public
     * @param callable $handler the initialization handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onInitiation(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('requestInitiated', $handler);

        return $this;
    }

    /**
     * This method adds a failure handler.
     *
     * @access public
     * @param callable $handler the failure handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onFailure(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('requestFailed', $handler);

        return $this;
    }

    /**
     * This method adds an opening handler.
     *
     * @access public
     * @param callable $handler the opening handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onOpening(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('requestOpened', $handler);

        return $this;
    }

    /**
     * This method adds a success handler.
     *
     * @access public
     * @param callable $handler the success handler to be added
     * @return TCP\RequestBroker a reference to this class
     */
    public function onSuccess(callable $handler): TCP\RequestBroker
    {
        $this->server->subscribe('requestSucceeded', $handler);

        return $this;
    }

}
