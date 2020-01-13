<?php

namespace App\Commands;

use Exception;
use CodeIgniter\CLI\CLI;
use Config\Database;

use Torch\Util\CLI as CLIUtil;
use Torch\Util\FileSystem;

class TorchGenerateView extends TorchBaseCommand
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
    protected $name = 'torch:generate:view';
    
    /**
     * Description
     * 
     * @var string
     */
    protected $description = 'Admin generate view.';
    
    /**
     * Usage
     * 
     * @var string
     */
    protected $usage = 'torch:generate:view [view] [options]';
    
    /**
     * Arguments
     * 
     * @var array
     */
    protected $arguments = [
        'view' => 'View file'
    ];
    
    /**
     * Options
     * 
     * @var array
     */
    protected $options = [
        '--template' => 'Use template for content',
        '--db-table' => 'Use db table to get fields info',
    ];
    
    /**
     * Predefined templates
     * 
     * @var array
     */
    protected $predefinedTemplates = [
        'index',
        'create',
        'edit',
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
        
        $template = CLI::getOption('template');
        $dbTable = CLI::getOption('dbtable');
        $view = array_shift($cmdParams);
        $view = str_replace('\\', '/', $view);
        
        helper('inflector');
        
        CLIUtil::showErrorAndExitIfConditionFailed(
            $view, 
            'Missing view!');
        
        $viewSegments = explode('/', $view);
        $viewFile = array_pop($viewSegments);
        $viewPath = join('/', $viewSegments);
        
        $path = $config->viewPath . $viewPath;
        $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        
        FileSystem::createDirectoryIfNotExists($path);
        
        $file = $path . $viewFile .'.php';
        
        CLIUtil::showErrorAndExitIfConditionSucceed(
            file_exists($file), 
            'View file "'. $file .'" exists!');
        
        $fields = array();
        
        if ($dbTable)
        {
            $fields = Database::connect()->_fieldData($dbTable);
        }
        
        $viewContent = $this->generateView(array(
            'view' => $view,
            'template' => $template,
            'fields' => $fields
        ));
        
        CLIUtil::showErrorAndExitIfConditionSucceed(
            false === file_put_contents($file, $viewContent), 
            'Failed to write file "'. $file .'"!');
        
        CLI::write('View file created: '. CLI::color($file, 'green'));
    }
    
    /**
     * Generate view
     * 
     * @param array $settings
     * @return string View content
     */
    protected function generateView(array $settings) : string
    {
        $settings = array_merge(array(
            'view' => '',
            'template' => '',
            'fields' => array(),
        ), $settings);
        
        $viewContent = '';
        
        if ($settings['template'])
        {
            if (in_array($settings['template'], $this->predefinedTemplates))
            {
                $config = config('Torch');
                $baseView = $settings['view'];
                $baseView = preg_replace('@/|\\\@', '-', $baseView);
                $baseViewSegments = explode('-', $baseView);
                $routeSegments = explode('/', $settings['view']);
                $baseRoute = $routeSegments[0];
                $entity = reset($baseViewSegments);
                $entityPlural = preg_replace('@-@', ' ', $baseRoute);
                $entityPlural = ucfirst(plural($entityPlural));
                $entitySingular = preg_replace('@-@', ' ', $baseRoute);
                $entitySingular = ucfirst(singular($entitySingular));
                $routeNamePrefix = $config->routeNamePrefix
                    ? $config->routeNamePrefix .'-'
                    : '';
                $viewContent = call_user_func(array($this, 'generate'. ucfirst($settings['template'])), [
                    'route_edit' => $routeNamePrefix . $baseRoute .'-edit',
                    'route_create' => $routeNamePrefix . $baseRoute .'-create',
                    'route_delete' => $routeNamePrefix . $baseRoute .'-delete',
                    'entity_singular' => $entitySingular,
                    'entity_plural' => $entityPlural,
                    'header' => $config->viewLayoutHeaderNamespace,
                    'footer' => $config->viewLayoutFooterNamespace,
                    'fields' => $settings['fields'],
                    'base_route' => $baseRoute,
                ]);
            }
            else if (file_exists($settings['template']))
            {
                $viewContent = file_get_contents($settings['template']);
            }
            else
            {
                $viewContent = $settings['template'];
            }
        }
        
        return $viewContent;
    }
    
    /**
     * Generate index view
     * 
     * @param array $settings
     * @return string View content
     */
    protected function generateIndex(array $settings) : string
    {
        $settings = array_merge(array(
            'route_edit' => '',
            'route_create' => '',
            'route_delete' => '',
            'header' => '',
            'footer' => '',
            'entity_singular' => '',
            'entity_plural' => '',
            'fields' => [],
            'base_route' => '',
        ), $settings);
        
        $content = '<?php echo view(\''. $settings['header'] .'\'); ?>
<div class="row">
    <div class="col-sm-12">
        <h1 class="display-3">'. $settings['entity_plural'] .'</h1>
        <?php if (isset($messages)) { ?>
            <div class="messages">
                <?php foreach ($messages as $type => $messagesOfType) { ?>
                    <?php foreach ($messagesOfType as $message) { ?>
                        <div class="alert alert-<?php echo $type; ?>" role="alert"><?php echo $message; ?></div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
        <div>
            <a style="margin: 19px;" href="<?php echo route_to(\''. $settings['route_create'] .'\'); ?>" class="btn btn-primary">New '. $settings['entity_singular'] .'</a>
        </div>
        <?php if ($items) { ?>
            <table class="table table-striped">
                <thead>
                    <tr>';
        
        foreach ($settings['fields'] as $field)
        {
            $fieldName = humanize($field->name);
            $content .= '
                        <td>'. $fieldName .'</td>';
        }
        
        $content .= '
                        <td colspan="2">Actions</td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item) { ?>
                    <tr>
                        <?php foreach ($item as $field => $value) { ?>
                            <td><?php echo $value; ?></td>
                        <?php } ?>
                        <td>
                            <a href="<?php echo route_to(\''. $settings['route_edit'] .'\', $item[\'id\']); ?>" class="btn btn-primary">Edit</a>
                        </td>
                        <td>
                            <a href="<?php echo route_to(\''. $settings['route_delete'] .'\', $item[\'id\']); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php if ($pager) { ?>
                <?php echo $pager->links(); ?>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<?php echo view(\''. $settings['footer'] .'\'); ?>
';
        return $content;
    }
    
    /**
     * Generate create view
     * 
     * @param array $settings
     * @return string View content
     */
    protected function generateCreate(array $settings)
    {
        $settings = array_merge(array(
            'route_edit' => '',
            'route_create' => '',
            'route_delete' => '',
            'header' => '',
            'footer' => '',
            'entity_singular' => '',
            'entity_plural' => '',
            'fields' => [],
        ), $settings);
        
        $content = '<?php echo view(\''. $settings['header'] .'\'); ?>
<div class="row">
    <div class="col-sm-8 offset-sm-2">
        <h1 class="display-3">Add '. $settings['entity_singular'] .'</h1>
        <?php if (isset($errors)) { ?>
            <div class="messages">
                <?php foreach ($errors as $errorField => $errorMessage) { ?>
                    <div class="alert alert-danger" role="alert"><?php echo $errorMessage; ?></div>
                <?php } ?>
            </div>
        <?php } ?>
        <div>
            <form method="post" action="<?php echo route_to(\''. $settings['route_create'] .'\'); ?>">';
        
        foreach ($settings['fields'] as $field)
        {
            if ($field->name == 'id')
            {
                continue;
            }
            $fieldName = humanize($field->name);
            $fieldIdAttribute = dasherize($field->name);
            $fieldNameAttribute = dasherize($field->name);
            $content .= '
                <div class="form-group">
                    <label for="'. $fieldIdAttribute .'">'. $fieldName .':</label>
                    <input type="text" class="form-control" name="'. $fieldNameAttribute .'" id="'. $fieldIdAttribute .'" value="<?php echo $formData[\''. $fieldNameAttribute .'\']; ?>" />
                </div>';
        }
        
        $content .= '
                <button type="submit" class="btn btn-primary">Add '. $settings['entity_singular'] .'</button>
            </form>
        </div>
    </div>
</div>
<?php echo view(\''. $settings['footer'] .'\'); ?>
';
        return $content;
    }
    
    /**
     * Generate edit view
     * 
     * @param array $settings
     * @return string View content
     */
    protected function generateEdit(array $settings)
    {
        $settings = array_merge(array(
            'route_edit' => '',
            'route_create' => '',
            'route_delete' => '',
            'header' => '',
            'footer' => '',
            'entity_singular' => '',
            'entity_plural' => '',
            'fields' => [],
        ), $settings);
        
        $content = '<?php echo view(\''. $settings['header'] .'\'); ?>
<div class="row">
    <div class="col-sm-8 offset-sm-2">
        <h1 class="display-3">Edit '. $settings['entity_singular'] .'</h1>
        <?php if (isset($errors)) { ?>
            <div class="messages">
                <?php foreach ($errors as $errorField => $errorMessage) { ?>
                    <div class="alert alert-danger" role="alert"><?php echo $errorMessage; ?></div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if (isset($messages)) { ?>
            <div class="messages">
                <?php foreach ($messages as $type => $messagesOfType) { ?>
                    <?php foreach ($messagesOfType as $message) { ?>
                        <div class="alert alert-<?php echo $type; ?>" role="alert"><?php echo $message; ?></div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
        <div>
            <form method="post" action="<?php echo route_to(\''. $settings['route_edit'] .'\', $formData[\'id\']); ?>">';
        
        foreach ($settings['fields'] as $field)
        {
            if ($field->name == 'id')
            {
                continue;
            }
            $fieldName = humanize($field->name);
            $fieldIdAttribute = dasherize($field->name);
            $fieldNameAttribute = dasherize($field->name);
            $content .= '
                <div class="form-group">
                    <label for="'. $fieldIdAttribute .'">'. $fieldName .':</label>
                    <input type="text" class="form-control" name="'. $fieldNameAttribute .'" id="'. $fieldIdAttribute .'" value="<?php echo $formData[\''. $fieldNameAttribute .'\']; ?>" />
                </div>';
        }
        
        $content .= '
                <input type="hidden" name="id" value="<?php echo $formData[\'id\']; ?>" />
               <button type="submit" class="btn btn-primary">Edit '. $settings['entity_singular'] .'</button>
            </form>
        </div>
    </div>
</div>
<?php echo view(\''. $settings['footer'] .'\'); ?>
';
        
        return $content;
    }
}
