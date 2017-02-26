<?php

declare(strict_types = 1);

namespace Unicity\MappingService\Data {

	use \Unicity\Bootstrap;
	use \Unicity\Common;
	use \Unicity\Core;
	use \Unicity\IO;
	use \Unicity\Throwable;
	use \Unicity\VLD;

	class Validator extends Core\Object {

		/**
		 * This method executes the lookup.
		 *
		 * @access public
		 * @static
		 * @final
		 * @param string $script                                    the script's name (without the '.vld' suffix)
		 * @param Common\HashMap $input                             the input data to be validated
		 * @return Common\IMap                                      the feedback results
		 * @throws Throwable\FileNotFound\Exception                 indicates that no ".vld" could be found
		 */
		public static final function execute(string $script, Common\HashMap $input) : Common\IMap {
			$script = implode(DIRECTORY_SEPARATOR, preg_split('/(\\\|_)+/', trim($script, '\\'))) . '.vld';

			$file = null;
			foreach (Bootstrap::$classpaths as $directory) {
				$uri = Bootstrap::rootPath() . $directory . $script;
				if (file_exists($uri)) {
					$file = new IO\File($uri);
					break;
				}
				$uri = $directory . $script;
				if (file_exists($uri)) {
					$file = new IO\File($uri);
					break;
				}
			}
			if (is_null($file)) {
				throw new Throwable\FileNotFound\Exception('Unable to locate file for ":script".', array(':script' => $script));
			}

			$parser = new VLD\Parser(new \Unicity\IO\FileReader($file));
			return $parser->run($input);
		}

	}

}