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

namespace Unicity;

/**
 * This class provides a set of methods for handling class loading.
 *
 * @access public
 * @class
 * @final
 */
final class Bootstrap
{
    /**
     * This variable stores an array of classpaths that will be searched
     * when trying to load a class.
     *
     * @access public
     * @static
     * @var array an array of classpaths to search
     */
    public static $classpaths = [''];

    /**
     * This method returns the resource's URI for the given resource.
     *
     * @access public
     * @static
     * @param string $resource the resource
     * @return string the resource's URI
     */
    public static function getResourceURI($resource)
    {
        $resource = trim($resource, '\\');

        $components = preg_split('/(\\\|_)+/', $resource);

        $resource = implode(DIRECTORY_SEPARATOR, $components);

        foreach (static::$classpaths as $directory) {
            $uri = static::rootPath() . $directory . $resource;
            if (file_exists($uri)) {
                return $uri;
            }
            $uri = $directory . $resource;
            if (file_exists($uri)) {
                return $uri;
            }
        }

        return $resource;
    }

    /**
     * This method imports a class so that it can be used there-on-after.
     *
     * @access public
     * @static
     * @param string $className the name of the class to be imported
     */
    public static function import($className)
    {
        $className = trim($className, '\\');

        $components = preg_split('/(\\\|_)+/', $className);

        $fileName = implode(DIRECTORY_SEPARATOR, $components) . '.php';

        foreach (static::$classpaths as $directory) {
            $uri = static::rootPath() . $directory . $fileName;
            if (file_exists($uri)) {
                require_once($uri);
                $className = implode('\\', $components);
                if (method_exists($className, '__static')) {
                    $className::__static();
                }

                break;
            }
            $uri = $directory . $fileName;
            if (file_exists($uri)) {
                require_once($uri);
                $className = implode('\\', $components);
                if (method_exists($className, '__static')) {
                    $className::__static();
                }

                break;
            }
        }
    }

    /**
     * This method registers the specified classpaths (i.e. directories), which will be
     * used to search for classes.  A new registration will overwrite any previous
     * registrations.
     *
     * @access public
     * @static
     * @param array $classpaths the classpaths to be registered
     */
    public static function registerClasspaths(array $classpaths)
    {
        static::$classpaths = [];
        if ($classpaths !== null) {
            foreach ($classpaths as $directory) {
                static::$classpaths[] = (!in_array($directory, ['', '.']))
                    ? $directory . DIRECTORY_SEPARATOR
                    : '';
            }
        }
        if (empty(static::$classpaths)) {
            static::$classpaths[] = '';
        }
    }

    /**
     * This method returns the root path.
     *
     * @access public
     * @static
     * @return string the root path
     */
    public static function rootPath()
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        }

        return ROOT_PATH;
    }

    /**
     * This method is called when this file is first loaded and handles the basic
     * configurations.
     *
     * @access public
     * @static
     * @param array $args the arguments to be used
     * @return integer the status code message
     */
    public static function __main(array $args)
    {
        error_reporting(E_ALL | E_STRICT);

        spl_autoload_register(['\\Unicity\\Bootstrap', 'import']);

        return 0; // Success
    }

}

if (!defined('UNICITY_BOOTSTRAP_INSTALLED')) {
    define('UNICITY_BOOTSTRAP_INSTALLED', Bootstrap::__main((array)null));
}
