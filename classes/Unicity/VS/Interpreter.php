<?php

declare(strict_types = 1);

namespace Unicity\VS {

	use \Unicity\Bootstrap;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;
	use \Unicity\VS;

	class Interpreter extends Core\Object {

		/**
		 * This method executes the lookup.
		 *
		 * @access public
		 * @static
		 * @final
		 * @param Common\HashMap $input                             the input data to be processed
		 * @param string $script                                    the namespaced name to ".vld" file
		 * @return Common\ArrayList                                 the processed data
		 * @throws Throwable\FileNotFound\Exception                 indicates that no ".vld" could be found
		 */
		public static final function execute(Common\HashMap $input, string $script) : Common\ArrayList {
			$components = preg_split('/(\\\|_)+/', trim($script, '\\'));

			$filepath = implode(DIRECTORY_SEPARATOR, $components) . '.vld';

			$file = null;
			foreach (Bootstrap::$classpaths as $directory) {
				$uri = Bootstrap::rootPath() . $directory . $filepath;
				if (file_exists($uri)) {
					$file = new IO\File($uri);
					break;
				}
				$uri = $directory . $filepath;
				if (file_exists($uri)) {
					$file = new IO\File($uri);
					break;
				}
			}

			if (is_null($file)) {
				throw new Throwable\FileNotFound\Exception('Unable to locate file for ":script".', array(':script' => $script));
			}

			$parser = new VS\Parser(new \Unicity\IO\FileReader($file));
			return $parser->run($input);
		}

	}

}