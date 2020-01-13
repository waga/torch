<?php

namespace Torch\Controllers;

use Config\Services;
use Config\Database;
use CodeIgniter\Controller;

class BaseController extends Controller
{
    /**
     * Session messages key
     * 
     * @var string
     */
    const SESSION_MESSAGES_KEY = 'messages';
    
    /**
     * Message type warning
     * 
     * @var string
     */
    const MESSAGE_TYPE_WARNING = 'warning';
    
    /**
     * Default message type
     * 
     * @var string
     */
    const DEFAULT_MESSAGE_TYPE = self::MESSAGE_TYPE_WARNING;
    
    /**
     * Session user key
     * 
     * @var string
     */
    const SESSION_USER_KEY = 'user';
    
    /**
     * Request
     * 
     * @var \CodeIgniter\HTTP\IncomingRequest
     */
    protected $request;
    
    /**
     * Session
     * 
     * @var \CodeIgniter\Session\Session
     */
    protected $session;
    
    /**
     * Database
     * 
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $database;
    
    /**
     * Router
     * 
     * @var \CodeIgniter\Router\Router
     */
    protected $router;
    
    /**
     * Validation
     * 
     * @var \CodeIgniter\Validation\Validation
     */
    protected $validation;
    
    /**
     * User login model
     * 
     * @var string
     */
    protected $userLoginModel = 'Torch\Models\BaseModel';
    
    /**
     * Index view
     * 
     * @var string
     */
    protected $indexView = 'Torch\Views\index';
    
    /**
     * User login view
     * 
     * @var string
     */
    protected $userLoginView = 'Torch\Views\login';
    
    /**
     * User register view
     * 
     * @var string
     */
    protected $userRegisterView = 'Torch\Views\register';
    
    /**
     * User success login route
     * 
     * @var string
     */
    protected $userSuccessLoginRoute = 'admin-index';
    
    /**
     * User success logout route
     * 
     * @var string
     */
    protected $userSuccessLogoutRoute = 'admin-index';
    
    /**
     * User success register route
     * 
     * @var string
     */
    protected $userSuccessRegisterRoute = 'admin-index';
    
    /**
     * Redirect route on not logged in
     * 
     * @var string
     */
    protected $redirectRouteOnNotLoggedIn = 'admin-login';
    
    /**
     * Skip auth check controller method
     * 
     * @var string
     */
    protected $skipAuthCheckControllerMethod = array(
        '\\'. __CLASS__ .'::login',
        '\\'. __CLASS__ .'::logout',
        '\\'. __CLASS__ .'::register',
    );
    
    /**
     * Class constructor
     * 
     */
    public function __construct()
    {
        $this->session = Services::session();
        $this->database = Database::connect();
        $this->request = Services::request();
        $this->router = Services::router();
        $this->validation = Services::validation();
        $this->authProtection();
    }
    
    /**
     * View
     * 
     * @param string $view
     * @param array $data
     * @return string
     */
    protected function view(string $view, 
        array $data = array())
    {
        $viewData = array(
            'user' => $this->session->get(self::SESSION_USER_KEY)
        ) + $data + array(
            'messages' => $this->getSessionMessages(null)
        );
        return view($view, $viewData);
    }
    
    /**
     * Request method is post
     * 
     * @return bool
     */
    protected function requestMethodIsPost()
    {
        return 'post' == $this->request->getMethod();
    }
    
    /**
     * Auth protection
     * 
     */
    protected function authProtection()
    {
        if ((!$this->session->has(self::SESSION_USER_KEY) || !$this->session->get(self::SESSION_USER_KEY)) && 
            !in_array(
                $this->router->controllerName() .'::'. $this->router->methodName(), 
                $this->skipAuthCheckControllerMethod)
            )
        {
            Services::redirectResponse(null, true)
                ->route($this->redirectRouteOnNotLoggedIn)
                ->send();
            exit;
        }
    }
    
    /**
     * Get session message
     * 
     * @param string $type
     * @return array
     */
    protected function getSessionMessages(string $type = self::DEFAULT_MESSAGE_TYPE) : array
    {
        $messages = array();
        
        if ($this->session->has(self::SESSION_MESSAGES_KEY))
        {
            $messages = $this->session->get(self::SESSION_MESSAGES_KEY);
        }
        
        if (!is_array($messages))
        {
            $messages = array();
        }
        
        if (null === $type)
        {
            $this->session->set(self::SESSION_MESSAGES_KEY, array());
            return $messages;
        }
        
        if (!$type || !is_string($type))
        {
            $type = self::DEFAULT_MESSAGE_TYPE;
        }
        
        $result = array();
        
        if (array_key_exists($type, $messages))
        {
            $result = $messages[$type];
            unset($messages[$type]);
        }
        
        $this->session->set(self::SESSION_MESSAGES_KEY, $messages);
        return $result;
    }
    
    /**
     * Set session message
     * 
     * @param string $message
     * @param string $type
     */
    protected function setSessionMessage(string $message, 
        string $type = self::DEFAULT_MESSAGE_TYPE)
    {
        $messages = array();
        
        if ($this->session->has(self::SESSION_MESSAGES_KEY))
        {
            $messages = $this->session->get(self::SESSION_MESSAGES_KEY);
        }
        
        if (!is_array($messages))
        {
            $messages = array();
        }
        
        if (!$type || !is_string($type))
        {
            $type = self::DEFAULT_MESSAGE_TYPE;
        }
        
        if (!array_key_exists($type, $messages))
        {
            $messages[$type] = array();
        }
        
        $messages[$type][] = $message;
        $this->session->set(self::SESSION_MESSAGES_KEY, $messages);
    }
    
    /**
     * Login
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function login()
	{
        if ($this->requestMethodIsPost())
        {
            $isValid = $this->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            
            if (!$isValid)
            {
                return $this->view($this->userLoginView, [
                    'errors' => $this->validation->getErrors()
                ]);
            }
            
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $password = md5($password);
            
            $user = (new $this->userLoginModel())
                ->login($email, $password);
            
            if (!$user)
            {
                return $this->view($this->userLoginView, [
                    'errors' => $this->validation->getErrors()
                ]);
            }
            
            $this->session->set(self::SESSION_USER_KEY, $user);
            return redirect()->route($this->userSuccessLoginRoute);
        }
        
        return $this->view($this->userLoginView);
	}
    
    /**
     * Logout
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
	{
        $this->session->destroy();
        return redirect()->route($this->userSuccessLogoutRoute);
	}
    
    /**
     * Register
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function register()
    {
        if ($this->requestMethodIsPost())
        {
            $isValid = $this->validate([
                'email' => 'required',
                'password' => 'required',
            ]);
            
            if (!$isValid)
            {
                return $this->view($this->userRegisterView, [
                    'errors' => $this->validation->getErrors()
                ]);
            }
            
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $password = md5($password);
            
            if (!$userId = (new $this->userLoginModel())
                ->register([
                    'email' => $email,
                    'password' => $password,
                ]))
            {
                return $this->view($this->userRegisterView, [
                    'errors' => $this->validation->getErrors()
                ]);
            }
            
            return redirect()->route($this->userSuccessRegisterRoute);
        }
        
        return $this->view($this->userRegisterView);
    }
    
    /**
     * Index
     * 
     * @return string
     */
	public function index()
	{
        return $this->view($this->indexView);
	}
}
