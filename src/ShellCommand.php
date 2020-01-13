<?php

namespace Torch;

use Exception;

abstract class ShellCommand
{
    /**
     * Spark file
     * 
     * @var string
     */
    protected static $sparkFile;
    
    /**
     * Base command string
     * 
     * @var string
     */
    protected static $baseCommandString;
    
    /**
     * Command arguments
     * 
     * @var array
     */
    protected $arguments = array();
    
    /**
     * Command options
     * 
     * @var array
     */
    protected $options = array();
    
    /**
     * Class constructor
     * 
     */
    public function __construct()
    {
        if (!static::$sparkFile)
        {
            throw new Exception('Spark file location not set!');
        }
        static::$baseCommandString = 'php '. static::$sparkFile .' torch:generate';
    }
    
    /**
     * Add argument
     * 
     * @param string $key
     * @param string $argument
     * @return \Torch\ShellCommand
     */
    public function addArgument(string $key, string $argument)
    {
        $this->arguments[$key] = $argument;
        return $this;
    }
    
    /**
     * Get argument
     * 
     * @param string $key
     * @param string $defaultReturnValue
     * @return mixed Argument by key if available, 
     *  otherwise $defaultReturnValue
     */
    public function getArgument(string $key, $defaultReturnValue = null)
    {
        if (array_key_exists($key, $this->arguments))
        {
            return $this->arguments[$key];
        }
        return $defaultReturnValue;
    }
    
    /**
     * Add option
     * 
     * @param string $key
     * @param string $option
     * @return \Torch\ShellCommand
     */
    public function addOption(string $key, string $option)
    {
        $this->options[$key] = $option;
        return $this;
    }
    
    /**
     * Get option
     * 
     * @param string $key
     * @param string $defaultReturnValue
     * @return mixed Option by key if available, 
     *  otherwise $defaultReturnValue
     */
    public function getOption(string $key, $defaultReturnValue = null)
    {
        if (array_key_exists($key, $this->options))
        {
            return $this->options[$key];
        }
        return $defaultReturnValue;
    }
    
    /**
     * Render command string
     * 
     * @return string
     */
    abstract public function renderCommandString() : string;
    
    /**
     * Set spark file
     * 
     * @param string $sparkFile
     */
    public static function setSparkFile(string $sparkFile)
    {
        static::$sparkFile = $sparkFile;
    }
    
    /**
     * Set spark file
     * 
     * @param string $sparkFile
     */
    public static function getSparkFile()
    {
        return static::$sparkFile;
    }
    
    /**
     * Create shell command
     * 
     * @param array $arguments
     * @param array $options
     * @return \Torch\ShellCommand
     */
    public static function create(array $arguments = array(), 
        array $options = array())
    {
        $class = get_called_class();
        $instance = new $class;
        foreach ($arguments as $argument => $value)
        {
            $instance->addArgument($argument, $value);
        }
        foreach ($options as $option => $value)
        {
            $instance->addOption($option, $value);
        }
        return $instance;
    }
}
