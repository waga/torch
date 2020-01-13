<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;
use Config\Database;

use Torch\Util\CLI as CLIUtil;
use Torch\Util\FileSystem;
use Torch\Util\Inflector;

class TorchGenerateController extends TorchBaseCommand
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
    protected $name = 'torch:generate:controller';
    
    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Admin generate controller.';
    
    /**
     * Usage
     * 
     * @var string
     */
    protected $usage = 'torch:generate:controller [controller] [options]';
    
    /**
     * Arguments
     * 
     * @var array
     */
    protected $arguments = [
        'controller' => 'Controller class file'
    ];
    
    /**
     * Options
     * 
     * @var array
     */
    protected $options = [
        '--resource' => 'Generate as resource',
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
        $isResource = CLI::getOption('resource');
        $dbTable = CLI::getOption('dbtable');
        $controller = array_shift($params);
        
        helper('inflector');
        helper('filesystem');
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            $controller, 
            'Missing controller!');
        
        FileSystem::createDirectoryIfNotExists(
            $config->controllerPath);
        
        $controllerFile = $config->controllerPath . $controller .'.php';
        
        CLIUtil::showErrorAndExitIfConditionSucceed(
            file_exists($controllerFile), 
            'File "'. $controllerFile .'" exists!');
        
        $relatedView = Inflector::kebab($controller);
        
        $fields = array();
        
        if ($dbTable)
        {
            $fields = Database::connect()->_fieldData($dbTable);
        }
        
        $routeNamePrefix = $config->routeNamePrefix
            ? $config->routeNamePrefix .'-'
            : '';
        $controllerContent = $this->generateController(array(
            'is_resource' => $isResource,
            'base_controller_namespace' => $config->baseControllerNamespace,
            'base_controller_class' => $config->baseControllerClass,
            'controller_class' => $controller,
            'controller_namespace' => $config->controllerNamespace,
            'related_model_class' => $controller,
            'related_model_namespace' => $config->modelNamespace,
            'related_model_alias' => $controller .'Model',
            'related_view' => $relatedView,
            'related_view_namespace' => $config->viewNamespace,
            'fields' => $fields,
            'entity_singular' => Inflector::entitySingular($relatedView),
            'entity_plural' => Inflector::entityPlural($relatedView),
            'route_name_prefix' => $routeNamePrefix,
        ));
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            write_file($controllerFile, $controllerContent), 
            'Failed to write file "'. $controllerFile .'"!');
        
        CLI::write('Controller file created: '. CLI::color($controllerFile, 'green'));
    }
    
    /**
     * Generate controller
     * 
     * @param array $settings
     * @return string Controller content
     */
    protected function generateController(array $settings) : string
    {
        $settings = array_merge(array(
            'is_resource' => false,
            'base_controller_namespace' => '',
            'base_controller_class' => '',
            'controller_class' => '',
            'controller_namespace' => '',
            'related_model_class' => '',
            'related_model_alias' => '',
            'related_model_namespace' => '',
            'related_view' => '',
            'related_view_namespace' => '',
            'fields' => array(),
            'entity_singular' => '',
            'entity_plural' => '',
            'route_name_prefix' => '',
        ), $settings);
        
        $controllerContent = '<?php

namespace '. $settings['controller_namespace'] .';

use CodeIgniter\Exceptions\PageNotFoundException;
use '. $settings['base_controller_namespace'] .'\\'. $settings['base_controller_class'] .';';

        if ($settings['is_resource'])
        {
            $controllerContent .= '
use '. $settings['related_model_namespace'] .'\\'. $settings['related_model_class'] .' as '. $settings['related_model_alias'] .';';
        }

        $controllerContent .= '

class '. $settings['controller_class'] .' extends '. $settings['base_controller_class'] .'
{';

        if ($settings['is_resource'])
        {
            $controllerContent .= '
    public function index()
    {
        $model = new '. $settings['related_model_alias'] .'();
        $items = $model->paginate(20);
        return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\index\', [
            \'items\' => $items,
            \'pager\' => $model->pager
        ]);
    }
    
    public function create()
    {
        if ($this->requestMethodIsPost())
        {';
            if ($settings['fields'])
            {
                $controllerContent .= '
            if (!$this->validate([';
                foreach ($settings['fields'] as $field)
                {
                    if ($field->name == 'id')
                    {
                        continue;
                    }
                    $controllerContent .= '
                \''. $field->name .'\' => \'required\',';
                }
                $controllerContent .= '
            ]))
            {
                return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\create\', [
                    \'formData\' => $this->request->getPost(),
                    \'errors\' => $this->validation->getErrors()
                ]);
            }
            
            if (!(new '. $settings['related_model_alias'] .'())
                ->insert([';
                
                foreach ($settings['fields'] as $field)
                {
                    if ($field->name == 'id')
                    {
                        continue;
                    }
                    $controllerContent .= '
                    \''. $field->name .'\' => $this->request->getPost(\''. $field->name .'\'),';
                }
                
                $controllerContent .= '
                ]))
            {
                return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\create\', [
                    \'formData\' => $this->request->getPost(),
                    \'errors\' => [
                        \'Failed to save data\'
                    ]
                ]);
            }
            ';
            }
            
            $controllerContent .= '
            $this->setSessionMessage(\''. $settings['entity_singular'] .' saved successfully\', \'success\');
            return redirect()->route(\''. $settings['route_name_prefix'] . $settings['related_view'] .'-index\');
        }
        
        return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\create\', [';
            
            if ($settings['fields'])
            {
                $controllerContent .= '
            \'formData\' => [';
                foreach ($settings['fields'] as $field)
                {
                    if ($field->name == 'id')
                    {
                        continue;
                    }
                    $controllerContent .= '
                \''. $field->name .'\' => \'\',';
                }
                $controllerContent .= '
            ],';
            }
            
            $controllerContent .= '
            \'errors\' => array()
        ]);
    }
    
    public function edit($id)
    {
        if (!$item = (new '. $settings['related_model_alias'] .'())->find($id))
        {
            throw new PageNotFoundException();
        }
        
        if ($this->requestMethodIsPost())
        {';
            if ($settings['fields'])
            {
                $controllerContent .= '
            if (!$this->validate([';
                
                foreach ($settings['fields'] as $field)
                {
                    $controllerContent .= '
                \''. $field->name .'\' => \'required\',';
                }
                
                $controllerContent .= '
            ]))
            {
                return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\edit\', [
                    \'formData\' => $this->request->getPost(),
                    \'errors\' => $this->validation->getErrors()
                ]);
            }
            
            if (!(new '. $settings['related_model_alias'] .'())
                ->update($this->request->getPost(\'id\'), [';
                
                foreach ($settings['fields'] as $field)
                {
                    $controllerContent .= '
                    \''. $field->name .'\' => $this->request->getPost(\''. $field->name .'\'),';
                }
                
                $controllerContent .= '
                ]))
            {
                return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\edit\', [
                    \'formData\' => $this->request->getPost(),
                    \'errors\' => [
                        \'Failed to save data\'
                    ]
                ]);
            }
            ';
            }
            
            $controllerContent .= '
            $this->setSessionMessage(\''. $settings['entity_singular'] .' edited successfully\', \'success\');
            return redirect()->route(\''. $settings['route_name_prefix'] . $settings['related_view'] .'-edit\', [
                $this->request->getPost(\'id\')
            ]);
        }
        
        return $this->view(\''. $settings['related_view_namespace'] .'\\'. $settings['related_view'] .'\edit\', [
            \'formData\' => $item,
            \'errors\' => array()
        ]);
    }
    
    public function delete($id)
    {
        if (!(new '. $settings['related_model_alias'] .'())->delete($id))
        {
            throw new PageNotFoundException();
        }
        $this->setSessionMessage(\''. $settings['entity_singular'] .' deleted successfully\', \'success\');
        return redirect()->route(\''. $settings['route_name_prefix'] . $settings['related_view'] .'-index\');
    }
';
        }
        else
        {
            $controllerContent .= PHP_EOL;
        }
        
        $controllerContent .= '}
';
        return $controllerContent;
    }
}
