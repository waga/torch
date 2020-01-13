<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Torch extends BaseConfig
{
    /**
     * Base path
     * 
     * @var string
     */
    public $basePath = APPPATH .'Libraries/Torch/';
    
    /**
     * Controller path
     * 
     * @var string
     */
    public $controllerPath = APPPATH .'Libraries/Torch/Controllers/';
    
    /**
     * Controller namespace
     * 
     * @var string
     */
    public $controllerNamespace = 'App\Libraries\Torch\Controllers';
    
    /**
     * Base controller namespace
     * 
     * @var string
     */
    public $baseControllerNamespace = 'Torch\Controllers';
    
    /**
     * Base controller class
     * 
     * @var string
     */
    public $baseControllerClass = 'BaseController';
    
    /**
     * Model path
     * 
     * @var string
     */
    public $modelPath = APPPATH .'Libraries/Torch/Models/';
    
    /**
     * Model namespace
     * 
     * @var string
     */
    public $modelNamespace = 'App\Libraries\Torch\Models';
    
    /**
     * Base model namespace
     * 
     * @var string
     */
    public $baseModelNamespace = 'Torch\Models';
    
    /**
     * Base model class
     * 
     * @var string
     */
    public $baseModelClass = 'BaseModel';
    
    /**
     * View path
     * 
     * @var string
     */
    public $viewPath = APPPATH .'Libraries/Torch/Views/';
    
    /**
     * View namespace
     * 
     * @var string
     */
    public $viewNamespace = 'App\Libraries\Torch\Views';
    
    /**
     * View layout header namespace
     * 
     * @var string
     */
    public $viewLayoutHeaderNamespace = 'App\Libraries\Torch\Views\layout\header';
    
    /**
     * View layout footer namespace
     * 
     * @var string
     */
    public $viewLayoutFooterNamespace = 'App\Libraries\Torch\Views\layout\footer';
    
    /**
     * Libraries torch config path
     * 
     * @var string
     */
    public $librariesTorchConfigPath = APPPATH .'Libraries/Torch/Config/';
    
    /**
     * Libraries torch config routes file
     * 
     * @var string
     */
    public $librariesTorchConfigRoutesFile = 'Routes.php';
    
    /**
     * Route url prefix
     * 
     * @var string
     */
    public $routeUrlPrefix = 'admin';
    
    /**
     * Route name prefix
     * 
     * @var string
     */
    public $routeNamePrefix = 'admin';
}
