<?php

namespace Torch\Util;

class FileSystem
{
    /**
     * Copy file
     * 
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyFile(string $source, 
        string $destination) : bool
    {
        return copy($source, $destination);
    }
    
    /**
     * Create directory
     * 
     * @param string $dir
     * @return bool
     */
    public static function createDirectory(string $dir) : bool
    {
        return mkdir($dir, 0755, true);
    }
    
    /**
     * Create directory if not exists
     * 
     * @param string $dir
     * @return bool|null
     */
    public static function createDirectoryIfNotExists(string $dir) : ?bool
    {
        if (!is_dir($dir))
        {
            return self::createDirectory($dir);
        }
        return null;
    }
    
    /**
     * Copy directory structure
     * 
     * @param string $sourceDir
     * @param string $destinationDir
     */
    public static function copyDirectoryStructure(
        string $sourceDir, 
        string $destinationDir)
    {
        $files = array_diff(
            scandir($sourceDir), 
            array('.', '..'));
        foreach ($files as $file)
        {
            if (is_dir($sourceDir . $file))
            {
                self::createDirectoryIfNotExists($destinationDir . $file);
                self::copyDirectoryStructure($sourceDir . $file . DIRECTORY_SEPARATOR, 
                    $destinationDir . $file . DIRECTORY_SEPARATOR);
            }
        }
    }
    
    /**
     * Copy file structure
     * 
     * @param string $sourceDir
     * @param string $destinationDir
     */
    public static function copyFileStructure(
        string $sourceDir, 
        string $destinationDir)
    {
        $files = array_diff(
            scandir($sourceDir), 
            array('.', '..'));
        foreach ($files as $file)
        {
            if (is_file($sourceDir . $file) && !is_dir($sourceDir . $file))
            {
                self::copyFile($sourceDir . $file, $destinationDir . $file);
            }
            else if (is_dir($sourceDir . $file))
            {
                self::copyFileStructure($sourceDir . $file . DIRECTORY_SEPARATOR, 
                    $destinationDir . $file . DIRECTORY_SEPARATOR);
            }
        }
    }
}
