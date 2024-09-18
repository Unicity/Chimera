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

namespace Unicity\Multithreading;

class ThreadWorker extends \Thread
{
    /**
     * This variable stores a reference to the closure to be ran.
     *
     * @access protected
     * @var callable
     */
    protected $runnable;

    /**
     * This constructor initializes the class with the specified closure to be ran.
     *
     * @access public
     * @param callable $runnable the closure to be ran
     */
    public function __construct(callable $runnable)
    {
        $this->runnable = $runnable;
    }

    /**
     * This method runs the closure as a thread.
     *
     * @access public
     */
    public function run()
    {
        $runnable = $this->runnable;
        $runnable();
    }

}
