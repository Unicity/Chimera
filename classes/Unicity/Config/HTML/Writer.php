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

namespace Unicity\Config\HTML {

	use \Unicity\Config;
	use \Unicity\IO;
	use \Unicity\Minify;

	/**
	 * This class is used to write a collection to an HTML file.
	 *
	 * @access public
	 * @class
	 * @package Config
	 */
	class Writer extends Config\Writer {

		/**
		 * This constructor initializes the class with the specified data.
		 *
		 * @access public
		 * @param mixed $data                                       the data to be written
		 */
		public function __construct($data) {
			$this->data = static::useArrays($data);
			$this->metadata = array(
				'declaration' => true,
				'ext' => '.html',
				'mime' => 'text/html',
				'minify' => array(),
				'template' => '',
				'url' => null,
			);
		}

		/**
		 * This method renders the data for the writer.
		 *
		 * @access public
		 * @return string                                           the data as string
		 * @throws \Exception                                       indicates a problem occurred
		 *                                                          when generating the template
		 */
		public function render() : string {
			$declaration = ($this->metadata['declaration'])
				? '<!DOCTYPE html>' . "\n"
				: '';
			if (!empty($this->metadata['template'])) {
				$file = new IO\File($this->metadata['template']);
				$mustache = new \Mustache_Engine(array(
					'loader' => new \Mustache_Loader_FilesystemLoader($file->getFilePath()),
					'escape' => function($string) {
						return htmlentities($string);
					},
				));
				ob_start();
				try {
					echo $declaration;
					echo $mustache->render($file->getFileName(), $this->data);
				}
				catch (\Exception $ex) {
					ob_end_clean();
					throw $ex;
				}
				$template = ob_get_clean();
				//if (!empty($this->metadata['minify'])) {
				//	$template = Minify\HTML::minify($template, $this->metadata['minify']);
				//}
				return $template;
			}
			return $declaration;
		}

	}

}