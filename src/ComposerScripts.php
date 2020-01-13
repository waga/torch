<?php

namespace Torch;

use Composer\Factory;
use Composer\Script\Event;

use Torch\Util\FileSystem;

class ComposerScripts
{
    /**
     * Default app folder
     * 
     * @var string
     */
    const DEFAUL_APP_FOLDER = 'app';
    
    /**
     * Argument app folder
     * 
     * @var string
     */
    const ARGUMENT_APP_FOLDER = '--app-folder';
    
    /**
     * Libraries torch config dir
     * 
     * @var string
     */
    const LIBRARIES_TORCH_CONFIG_DIR = 'Libraries/Torch/Config/';
    
    /**
     * Libraries torch views dir
     * 
     * @var string
     */
    const LIBRARIES_TORCH_VIEWS_DIR = 'Libraries/Torch/Views/';
    
    /**
     * Libraries torch config routes
     * 
     * @var string
     */
    const LIBRARIES_TORCH_CONFIG_ROUTES = 'Routes.php';
    
    /**
     * App commands dir
     * 
     * @var string
     */
    const APP_COMMANDS_DIR = 'Commands';
    
    /**
     * App views dir
     * 
     * @var string
     */
    const APP_VIEWS_DIR = 'Views';
    
    /**
     * App config dir
     * 
     * @var string
     */
    const APP_CONFIG_DIR = 'Config';
    
    /**
     * App config torch
     * 
     * @var string
     */
    const APP_CONFIG_TORCH = 'Torch.php';
    
    /**
     * Torch commands dir
     * 
     * @var string
     */
    const TORCH_COMMANDS_DIR = 'AppCommands';
    
    /**
     * Torch views dir
     * 
     * @var string
     */
    const TORCH_VIEWS_DIR = 'Views';
    
    /**
     * Torch config dir
     * 
     * @var string
     */
    const TORCH_CONFIG_DIR = 'Config';
    
    /**
     * Torch config torch
     * 
     * @var string
     */
    const TORCH_CONFIG_TORCH = 'Torch.php';
    
    /**
     * Torch config routes
     * 
     * @var string
     */
    const TORCH_CONFIG_ROUTES = 'Routes.php';
    
    /**
     * Cli arguments
     * 
     * @var array
     */
    protected static $cliArguments = array();
    
    /**
     * Composer path
     * 
     * @var string
     */
    protected static $composerPath;
    
    /**
     * App path
     * 
     * @var string
     */
    protected static $appPath;
    
    /**
     * Post update
     * 
     * @param Composer\Script\Event $event
     */
    public static function postUpdate(Event $event)
    {
        self::$cliArguments = self::parseArguments($event->getArguments(), array(
            self::ARGUMENT_APP_FOLDER => self::DEFAUL_APP_FOLDER
        ), array(
            self::ARGUMENT_APP_FOLDER => self::DEFAUL_APP_FOLDER
        ));
        
        self::$composerPath = self::discoverComposerFile();
        self::$appPath = self::discoverAppPath();
        
        self::createDirectories();
        self::copyCommands();
        self::copyConfigs();
        self::copyViews();
    }
    
    /**
     * Parse arguments
     * 
     * @param array $arguments
     * @param array $defaults
     * @param array $empty
     * @return array 
     */
    protected static function parseArguments(
        array $arguments, 
        array $defaults = array(),
        array $empty = array()
    ) : array
    {
        $keys = array();
        $values = array();
        array_walk($arguments, function($argument) use (&$keys, &$values, $empty) {
            $argumentSegments = explode('=', $argument);
            $key = array_shift($argumentSegments);
            $value = array_shift($argumentSegments);
            if (empty($value) && array_key_exists($key, $empty))
            {
                $value = $empty[$key];
            }
            $keys[] = $key;
            $values[] = $value;
        });
        return array_merge($defaults, array_combine($keys, $values));
    }
    
    /**
     * Discover composer file
     * 
     * @return string
     */
    protected static function discoverComposerFile()
    {
        return realpath(Factory::getComposerFile());
    }
    
    /**
     * Discover app path
     * 
     * @return string
     */
    protected static function discoverAppPath()
    {
        return dirname(self::$composerPath) 
            . DIRECTORY_SEPARATOR
            . rtrim(self::$cliArguments[self::ARGUMENT_APP_FOLDER], '\/')
            . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Ctrate directories
     * 
     */
    protected static function createDirectories()
    {
        FileSystem::createDirectoryIfNotExists(self::$appPath 
            . self::APP_COMMANDS_DIR);
        FileSystem::createDirectoryIfNotExists(
            self::$appPath . self::LIBRARIES_TORCH_CONFIG_DIR);
        FileSystem::copyDirectoryStructure(
            __DIR__ . DIRECTORY_SEPARATOR 
                . self::TORCH_VIEWS_DIR . DIRECTORY_SEPARATOR,
            self::$appPath . self::LIBRARIES_TORCH_VIEWS_DIR);
    }
    
    /**
     * Copy commands
     * 
     */
    protected static function copyCommands()
    {
        $sourceDir = __DIR__ . DIRECTORY_SEPARATOR 
            . self::TORCH_COMMANDS_DIR . DIRECTORY_SEPARATOR;
        $destinationDir = self::$appPath 
            . self::APP_COMMANDS_DIR . DIRECTORY_SEPARATOR;
        $files = array_diff(
            scandir($sourceDir), 
            array('.', '..'));
        foreach ($files as $file)
        {
            copy($sourceDir . $file, $destinationDir . $file);
        }
    }
    
    /**
     * Copy configs
     * 
     */
    protected static function copyConfigs()
    {
        $configFile = self::$appPath . DIRECTORY_SEPARATOR
            . self::APP_CONFIG_DIR . DIRECTORY_SEPARATOR 
            . self::APP_CONFIG_TORCH;
        if (!file_exists($configFile))
        {
            copy(__DIR__ . DIRECTORY_SEPARATOR
                . self::TORCH_CONFIG_DIR . DIRECTORY_SEPARATOR 
                . self::TORCH_CONFIG_TORCH, $configFile);
        }
        
        $configFile = self::$appPath 
            . self::LIBRARIES_TORCH_CONFIG_DIR 
            . self::LIBRARIES_TORCH_CONFIG_ROUTES;
        if (!file_exists($configFile))
        {
            copy(__DIR__ . DIRECTORY_SEPARATOR
                . self::TORCH_CONFIG_DIR . DIRECTORY_SEPARATOR 
                . self::TORCH_CONFIG_ROUTES, $configFile);
        }
    }
    
    /**
     * Copy views
     * 
     */
    protected static function copyViews()
    {
        FileSystem::copyFileStructure(
            __DIR__ . DIRECTORY_SEPARATOR 
                . self::TORCH_VIEWS_DIR . DIRECTORY_SEPARATOR,
            self::$appPath . self::LIBRARIES_TORCH_VIEWS_DIR);
    }
}
