<?php
namespace Neodock\Framework
{
	/**
	 * Filesystem
	 *
	 * Filesystem helper class and functions
	 *
	 * @version 1.0
	 * @author jeffzahorchak
	 */
	class Filesystem
	{
         /**
         * Retrieves a random image file from a given directory path
         *
         * @param string $directory The directory to select a random image file from
         * @return string File name
         * @throws \Exception
         */
        public static function GetRandomImageFileInDirectory($directory)
        {
            \Neodock\Framework\Debug::logMessage('Checking to see if ' . $directory . ' exists...');
            if (file_exists($directory))
            {
                \Neodock\Framework\Debug::logMessage('... ' . $directory . ' exists.');
                $orig = getcwd();
                chdir($directory);
                $return = glob('*.{jpg,gif,png}', GLOB_BRACE);
                if (count($return) > 0) 
                {
                    $return = $return[rand(0, count($return)-1)];
                } else {
                    $return = null;
                }
                chdir($orig);
                return $return;
            } else {
                \Neodock\Framework\Debug::logMessage('... ' . $directory . ' does not exist.');
                return null;
            }
        }
	}
}