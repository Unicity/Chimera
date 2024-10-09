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

use Unicity\ORM;
use Unicity\VLD;

class IterateStatement extends VLD\Parser\Definition\Statement
{
    public function get()
    {
        $control = (isset($this->args['control'])) ? $this->args['control']->get() : 'seq';
        if (isset($this->args['policy'])) {
            $policy = $this->args['policy']->get();
            if (!is_array($policy)) {
                $policy = [];
            }
        } else {
            $policy = [];
        }
        $path = $this->context->getCurrentPath();
        $block = $this->args['block']->get();
        $direction = $policy['direction'] ?? 'forward';
        $step = intval($policy['step'] ?? 1);

        $components = $this->context->getEntity()->getComponentAtPath($path);
        $length = count($components);

        $statements = [];
        if ($direction === 'reverse') {
            for ($i = $length - 1; $i >= 0; $i -= $step) {
                $statements[] = new VLD\Parser\Definition\ContextStatement($this->context, [
                    'path' => ORM\Query::appendIndex($path, $i),
                    'block' => $block,
                ]);
            }
        } else {
            for ($i = 0; $i < $length; $i += $step) {
                $statements[] = new VLD\Parser\Definition\ContextStatement($this->context, [
                    'path' => ORM\Query::appendIndex($path, $i),
                    'block' => $block,
                ]);
            }
        }

        $class = VLD\Parser\Definition\Control::getControl($control);
        $object = new $class($this->context, $policy, $statements);

        return $object->get();
    }

}
