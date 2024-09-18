<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011 Spadefoot Team
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

namespace Unicity\Spring;

/**
 * This interface provides the contract for an initializing object.
 *
 * @access public
 * @interface
 * @package Spring
 *
 * @see http://docs.spring.io/spring/docs/current/javadoc-api/org/springframework/beans/factory/InitializingBean.html
 * @see http://www.mkyong.com/spring/spring-initializingbean-and-disposablebean-example/
 */
interface InitializingObject
{
    /**
     * This method is called object's properties have been set.
     *
     * @access public
     */
    public function afterPropertiesSet();

}
