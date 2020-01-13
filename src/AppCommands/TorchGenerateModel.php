<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;
use Config\Database;

use Torch\Util\CLI as CLIUtil;
use Torch\Util\FileSystem;

class TorchGenerateModel extends TorchBaseCommand
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
    protected $name = 'torch:generate:model';
    
    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Admin generate model.';
    
    /**
     * Usage
     * 
     * @var string
     */
    protected $usage = 'torch:generate:model [model]';
    
    /**
     * Arguments
     * 
     * @var array
     */
    protected $arguments = [
        'model' => 'Model class file',
    ];
    
    /**
     * Options
     * 
     * @var array
     */
    protected $options = [
        '--db-table' => 'Use db table to get fields info',
    ];
    
    /**
     * Run
     * 
     * @param array $params
     */
    public function run(array $params)
    {
        $config = config('Torch');
        $dbTable = CLI::getOption('dbtable');
        $model = array_shift($params);
        
        helper('inflector');
        helper('filesystem');
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            $model, 
            'Missing model!');
        
        FileSystem::createDirectoryIfNotExists(
            $config->modelPath);
        
        $modelFile = $config->modelPath . $model .'.php';
        
        CLIUtil::showErrorAndExitIfConditionSucceed(
            file_exists($modelFile),
            'File "'. $modelFile .'" exists!');
        
        $fields = array();
        
        if ($dbTable)
        {
            $fields = Database::connect()->_fieldData($dbTable);
        }
        
        $table = $dbTable ? $dbTable : plural(snake($model));
        
        $modelContent = $this->generateModel(array(
            'base_model_namespace' => $config->baseModelNamespace,
            'base_model_class' => $config->baseModelClass,
            'model_class' => $model,
            'model_namespace' => $config->modelNamespace,
            'table' => $table,
            'fields' => $fields,
        ));
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            write_file($modelFile, $modelContent), 
            'Failed to write file "'. $modelFile .'"!');
        
        CLI::write('Model file created: '. CLI::color($modelFile, 'green'));
    }
    
    /**
     * Generate model
     * 
     * @param array $settings
     * @return string Model content
     */
    protected function generateModel(array $settings) : string
    {
        $settings = array_merge(array(
            'base_model_namespace' => '',
            'base_model_class' => '',
            'model_class' => '',
            'model_namespace' => '',
            'table' => '',
            'fields' => [],
        ), $settings);
        
        $content = '<?php

namespace '. $settings['model_namespace'] .';

use '. $settings['base_model_namespace'] .'\\'. $settings['base_model_class'] .';

class '. $settings['model_class'] .' extends '. $settings['base_model_class'] .'
{
    protected $table = \''. $settings['table'] .'\';';
        
        if ($settings['fields'])
        {
            $fields = $settings['fields'];
            $fields = array_filter($fields, function($field)
            {
                if (in_array($field->name, [
                    'id'
                ]))
                {
                    return false;
                }
                return true;
            });
            $content .= '
    protected $allowedFields = [';
            foreach ($fields as $field)
            {
                $content .= '
        \''. $field->name .'\', ';
            }
            $content .= '
    ];';
        }
        
        $content .= '
}
';
        return $content;
    }
}
