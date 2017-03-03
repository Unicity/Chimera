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

namespace Unicity\VLD\Parser\Definition {

	use \Unicity\VLD;

	class OnStatement extends VLD\Parser\Definition\Statement {

		protected static $events = null;

		public function get() {
			if (in_array($this->args['event']->get(), static::getEvents())) {
				$object = new VLD\Parser\Definition\SeqControl($this->context, null, $this->args['block']->get());
				return $object->get();
			}
			return new VLD\Parser\Feedback();
		}

		protected static function getEvents() {
			if (static::$events === null) {
				static::$events = (isset($_SERVER['HTTP_X_EVENT_TYPE']))
					? array_map('trim', explode(';', $_SERVER['HTTP_X_EVENT_TYPE']))
					: array();
			}
			return static::$events;
		}

	}

}