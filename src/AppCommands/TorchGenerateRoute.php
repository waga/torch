<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;

use Torch\Util\CLI as CLIUtil;
use Torch\Util\FileSystem;

class TorchGenerateRoute extends TorchBaseCommand
{
    /**
     * Group
     * 
     * @var string
     */
    protected $group = 'Application admin tools for codeigniter v4';
    
    /**
     * Name
     * 
     * @var string
     */
    protected $name = 'torch:generate:route';
    
    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Admin generate route.';
    
    /**
     * Usage
     * 
     * @var string
     */
    protected $usage = 'torch:generate:route [url] [handler]';
    
    /**
     * Arguments
     * 
     * @var array
     */
    protected $arguments = [
        'url' => 'Url to match route',
        'handler' => 'Handler to execute when route is found',
    ];
    
    /**
     * Options
     * 
     * @var array
     */
    protected $options = [
        '--method' => 'Request method',
        '--as' => 'Route name',
    ];
    
    /**
     * Run
     * 
     * @param array $params
     */
    public function run(array $params)
    {
        $config = config('Torch');
        $cmdParams = $_SERVER['argv'];
        $cmdParams = array_filter($cmdParams);
        array_shift($cmdParams);// spark
        array_shift($cmdParams);// command name
        
        $as = CLI::getOption('as');
        $requestMethod = CLI::getOption('method') ?? 'get';
        
        $url = array_shift($cmdParams);
        $url = str_replace('//', '/', $url);
        $handler = array_shift($cmdParams);
        
        helper('inflector');
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            $url, 
            'Missing url parameter!');
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            $handler, 
            'Missing handler parameter!');
        
        FileSystem::createDirectoryIfNotExists(
            $config->librariesTorchConfigPath);
        
        $configRoutesFile = $config->librariesTorchConfigPath .'Routes.php';
        $namespacedHandler = $config->controllerNamespace .'\\'. $handler;
        
        $route = '$routes->'. $requestMethod .'(\''. $url .'\', \''. $namespacedHandler .'\'';
        $routeNamePrefix = $config->routeNamePrefix
            ? $config->routeNamePrefix .'-'
            : '';
        
        if ($as)
        {
            $route .= ', [\'as\' => \''. $routeNamePrefix . $as .'\']';
        }
        $route .= ');';
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            file_put_contents($configRoutesFile, $route . PHP_EOL, FILE_APPEND), 
            'Failed to write file "'. $configRoutesFile .'"!');
        
        CLI::write('Route "'. $requestMethod .'|'. $url .'" added to '. $configRoutesFile);
    }
}
