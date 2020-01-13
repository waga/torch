<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;
use Config\Database;

use Torch\Util\CLI as CLIUtil;
use Torch\ShellCommand;
use Torch\ShellCommand\TorchGenerateController;
use Torch\ShellCommand\TorchGenerateModel;
use Torch\ShellCommand\TorchGenerateView;
use Torch\ShellCommand\TorchGenerateRoute;

class TorchGenerate extends TorchBaseCommand
{
    /**
     * Spark file
     * 
     * @var string
     */
    const SPARK_FILE = APPPATH .'..'. DIRECTORY_SEPARATOR .'spark';
    
    /**
     * App config routes file
     * 
     * @var string
     */
    const APP_CONFIG_ROUTES_FILE = APPPATH .'Config/Routes.php';
    
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
    protected $name = 'torch:generate';
    
    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Admin generator.';
    
    /**
     * Usage
     * 
     * @var string
     */
    protected $usage = 'torch:generate';
    
    /**
     * Run
     * 
     * @param array $params
     */
    public function run(array $params)
    {
        $config = config('Torch');
        helper('inflector');
        $tables = Database::connect()->listTables();
        chdir(APPPATH .'../');
        ShellCommand::setSparkFile(static::SPARK_FILE);
        
        foreach ($tables as $table)
        {
            $this->generateController($table);
            $this->generateModel($table);
            $this->generateView($table, 'index', 'get');
            $this->generateView($table, 'create', 'get');
            $this->generateView($table, 'edit', 'get');
            $this->generateRoute($table, 'index', 'get');
            $this->generateRoute($table, 'create', 'get');
            $this->generateRoute($table, 'create', 'post');
            $this->generateRoute($table, 'edit', 'get');
            $this->generateRoute($table, 'edit', 'post');
            $this->generateRoute($table, 'delete', 'get');
        }
        
        $configRoutesContent = file_get_contents(self::APP_CONFIG_ROUTES_FILE);
        $requireStatement = 'require \''
            . $config->librariesTorchConfigPath 
            . $config->librariesTorchConfigRoutesFile .'\';';
        
        if (false === strpos($configRoutesContent, $requireStatement))
        {
            file_put_contents(self::APP_CONFIG_ROUTES_FILE, 
                PHP_EOL . $requireStatement . PHP_EOL, 
                FILE_APPEND);
            CLI::write('Add "'. $requireStatement .'" to "'. self::APP_CONFIG_ROUTES_FILE .'"');
        }
        else
        {
            CLI::write('Torch routes config already added in "'. self::APP_CONFIG_ROUTES_FILE .'"');
        }
    }
    
    /**
     * Generate controller
     * 
     * @param string $table
     */
    protected function generateController(string $table)
    {
        $controller = singular(pascalize(str_replace('-', '_', $table)));
        CLIUtil::executeCommandAndOutputResult(
            TorchGenerateController::create(
                ['controller' => $controller], 
                [
                    '--resource' => true, 
                    '--db-table' => $table
                ]
            )->renderCommandString()
        );
    }
    
    /**
     * Generate model
     * 
     * @param string $table
     */
    protected function generateModel(string $table)
    {
        $model = singular(pascalize(str_replace('-', '_', $table)));
        CLIUtil::executeCommandAndOutputResult(
            TorchGenerateModel::create(
                ['model' => $model], 
                ['--db-table' => $table]
            )->renderCommandString()
        );
    }
    
    /**
     * Generate view
     * 
     * @param string $table
     * @param string $template
     */
    protected function generateView(string $table, 
        string $template)
    {
        $view = dasherize(singular($table)) .'/'. $template;
        CLIUtil::executeCommandAndOutputResult(
            TorchGenerateView::create(
                ['view' => $view], 
                [
                    '--template' => $template, 
                    '--db-table' => $table
                ]
            )->renderCommandString()
        );
    }
    
    /**
     * Generate route
     * 
     * @param string $table
     * @param string $template
     * @param string $method
     */
    protected function generateRoute(string $table, 
        string $template, 
        string $method = 'get')
    {
        $config = config('Torch');
        $routeUrlPrefix = preg_replace('@/+@', '', $config->routeUrlPrefix);
        $tableDasherizedSingular = dasherize(singular($table));
        $url = '//'. $routeUrlPrefix .'//'. $tableDasherizedSingular .'//'. $template;
        $handlerParams = '';
        
        if (in_array($template, [
            'edit',
            'delete',
        ]))
        {
            $url .= '/(:segment)';
            $handlerParams = '/$1';
        }
        
        $handler = singular(pascalize(str_replace('-', '_', $table))) .'::'. $template;
        $as = $tableDasherizedSingular .'-'. $template;
        
        CLIUtil::executeCommandAndOutputResult(
            TorchGenerateRoute::create(
                [
                    'url' => $url, 
                    'handler' => $handler . $handlerParams
                ], 
                [
                    '--as' => $as, 
                    '--method' => $method
                ]
            )->renderCommandString()
        );
    }
}
