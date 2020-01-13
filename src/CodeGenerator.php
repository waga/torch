<?php

namespace Torch;

use Config\Services;
use CodeIgniter\View\Exceptions\ViewException;

class CodeGenerator
{
    /**
     * Template file
     * 
     * @var string
     */
    protected $template = '';
    
    /**
     * Template data
     * 
     * @var array
     */
    protected $data = array();
    
    /**
     * Class constructor
     * 
     * @param string $template
     * @param array $data
     */
    public function __construct($template = '', array $data = array())
    {
        $this->template = $template;
        $this->data = $data;
    }
    
    /**
     * Set template
     * 
     * @param string $template
     * @return \Torch\CodeGenerator
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
        return $this;
    }
    
    /**
     * Get template
     * 
     * @return string
     */
    public function getTemplate() : string
    {
        return $this->template;
    }
    
    /**
     * Set data
     * 
     * @param array $data
     * @return \Torch\CodeGenerator
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * Get data
     * 
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }
    
    /**
     * Generate code
     * 
     * @param array $data
     * @return string In case template exists load its content
     *   otherwise return template 
     */
    public function generate()
    {
        $loader = Services::locator();
        $file = $loader->locateFile($this->template, '', 'php');
        
        extract($this->data);
        
        if ($file)
        {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        
        return $this->template;
    }
}
