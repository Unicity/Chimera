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

namespace Unicity\EVT\EventWriter {

	use \Aws\Kinesis\KinesisClient;
	use \Unicity\EVT;

	class Kinesis extends EVT\EventWriter {

		/**
		 * This variable stores a reference to the Kinesis client.
		 *
		 * @access protected
		 * @var string
		 */
		protected $client;

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(array $metadata = array()) {
			parent::__construct($metadata);
			$this->client = KinesisClient::factory(array(
				'key' => $metadata['key'],
				'secret' => $metadata['secret'],
				'region'  => $metadata['region'],
			));
		}

		/**
		 * This destructor ensures that any resources are properly disposed.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->client);
		}

		/**
		 * This method writes an array of events to the event storage.
		 *
		 * @access public
		 * @param array $events                                     the events to be written
		 */
		public function write(array $events) {
			foreach ($events as $event) {
				$this->client->putRecord([
					'Data' => json_encode($event),
					'PartitionKey' => $this->metadata['partition'],
					'StreamName' => $this->metadata['stream'],
				]);
			}
		}

	}

}