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

	use \Unicity\EVT;

	class StdOut extends EVT\EventWriter {

		/**
		 * This constructor initializes the class with the specified resource.
		 *
		 * @access public
		 * @param array $metadata                                   the metadata to be set
		 */
		public function __construct(array $metadata = array()) {
			parent::__construct(array_merge(array(
				'eol' => "\n",
			), $metadata));
		}

		/**
		 * This method writes an array of events to the event storage.
		 *
		 * @access public
		 * @param array $events                                     the events to be written
		 */
		public function write(array $events) {
			foreach ($events as $event) {
				fwrite(STDOUT, json_encode($event) . $this->metadata['eol']);
			}
		}

	}

}