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

class SelectStatement extends VLD\Parser\Definition\Statement
{
    public function get()
    {
        $control = (isset($this->args['control'])) ? $this->args['control']->get() : 'seq';
        $policy = (isset($this->args['policy'])) ? $this->args['policy']->get() : null;
        $paths = (isset($this->args['paths'])) ? $this->args['paths']->get() : [];
        $paths = $this->context->getAbsolutePaths($paths);
        $block = $this->args['block']->get();

        $statements = [];
        foreach ($paths as $path) {
            $statements[] = new VLD\Parser\Definition\ContextStatement($this->context, [
                'path' => $path,
                'block' => $block,
            ]);
        }

        $class = VLD\Parser\Definition\Control::getControl($control);
        $object = new $class($this->context, $policy, $statements);

        return $object->get();
    }

}
