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

namespace Unicity\VLD\Parser\Definition;

use Unicity\VLD;

class EvalStatement extends VLD\Parser\Definition\Statement
{
    public function get()
    {
        $module = $this->args['module']->get();
        $config = $this->context->getModule($module);
        $policy = (isset($this->args['policy'])) ? $this->args['policy']->get() : ($config['policy'] ?? null);
        $paths = $this->context->getAbsolutePaths($this->args['paths']->get());
        $entity = $this->context->getEntity();
        $class = $config['class'];
        $object = new $class($policy);

        return call_user_func_array([$object, 'process'], [$entity, $paths]);
    }

}
