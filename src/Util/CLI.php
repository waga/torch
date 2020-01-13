<?php

namespace Torch\Util;

use CodeIgniter\CLI\CLI as CICLI;

class CLI
{
    /**
     * Show error and exit if condition succeed
     * 
     * @param mixed $condition
     * @param string $message
     */
    public static function showErrorAndExitIfConditionSucceed(
        $condition, 
        string $message)
    {
        if ($condition)
        {
            static::showErrorAndExit($message);
        }
    }
    
    /**
     * Show error and exit if condition failed
     * 
     * @param mixed $condition
     * @param string $message
     */
    public static function showErrorAndExitIfConditionFailed(
        $condition, 
        string $message)
    {
        if (!$condition)
        {
            static::showErrorAndExit($message);
        }
    }
    
    /**
     * Show error and exit
     * 
     * @param string $message
     */
    public static function showErrorAndExit(string $message)
    {
        CICLI::error($message);
        exit;
    }
    
    /**
     * Execute command
     * 
     * @param string $command
     */
    public static function executeCommand(string $command)
    {
        return shell_exec($command);
    }
    
    /**
     * Execute command and output result
     * 
     * @param string $command
     * @param mixed $filter
     */
    public static function executeCommandAndOutputResult(
        string $command, 
        $filter = 'static::defaultOutputFilter')
    {
        $output = static::executeCommand($command);
        if (is_callable($filter))
        {
            $output = call_user_func_array($filter, [$output]);
        }
        CICLI::write($output);
    }
    
    /**
     * Default output filter
     * 
     * @param string $output
     */
    protected static function defaultOutputFilter(string $output)
    {
        return preg_replace([
            '/CodeIgniter CLI Tool.*/',
            '/\n|\r\n/'
        ], '', $output);
    }
}
