<?php
/**
 * FileName: Route.php
 * Author: zyf
 * Date: 16/9/20 下午2:53
 * Brief:
 */
class System_Core_Route {

    const DEFAULT_CONTROLLER = "Index";
    const DEFAULT_ACTION = "Index";
    public $controller;
    public $action;

    private static $_instance = null;

    /**
     * 构造函数
     */
    private function __construct() {
        $this->setRoute();
    }

    /**
     * @return null|System_Core_Route
     */
    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $controller
     * @param $action
     */
    public function initDefault($controller, $action) {
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     *
     */
    public function setRoute() {
        $uri = System_Core_URI::getInstance()->detectUri();
        if($uri == "") {
            $this->setDefault();
        }
        else {
            $routeParams = explode('/', $uri);
            if(count($routeParams) == 1) {
                $this->controller = $routeParams[0];
                $this->action = self::DEFAULT_ACTION;
            }
            else {
                $this->controller = $routeParams[0];
                $this->action = $routeParams[1];
            }
        }
    }

    /**
     * @return string
     */
    public function fetchControllerClass() {
        return "Controller_" . $this->controller;
    }

    /**
     * @return string
     */
    public function fetchActionClass() {
        return "Action_" . $this->action;
    }

    /**
     * 设置默认路由路由
     */
    public function setDefault() {
        if(!$this->controller) {
            $this->controller = self::DEFAULT_CONTROLLER;
        }
        if(!$this->action) {
            $this->action = self::DEFAULT_ACTION;
        }
    }

    /**
     * Validates the supplied segments.  Attempts to determine the path to
     * the controller.
     *
     * @access	private
     * @param	array
     * @return	array
     */
    function _validate_request($segments)
    {
        if (count($segments) == 0)
        {
            return $segments;
        }

        // Does the requested controller exist in the root folder?
        if (file_exists(APPPATH.'controllers/'.$segments[0].'.php'))
        {
            return $segments;
        }

        // Is the controller in a sub-folder?
        if (is_dir(APPPATH.'controllers/'.$segments[0]))
        {
            // Set the directory and remove it from the segment array
            $this->set_directory($segments[0]);
            $segments = array_slice($segments, 1);

            if (count($segments) > 0)
            {
                // Does the requested controller exist in the sub-folder?
                if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].'.php'))
                {
                    if ( ! empty($this->routes['404_override']))
                    {
                        $x = explode('/', $this->routes['404_override']);

                        $this->set_directory('');
                        $this->set_class($x[0]);
                        $this->set_method(isset($x[1]) ? $x[1] : 'index');

                        return $x;
                    }
                    else
                    {
                        show_404($this->fetch_directory().$segments[0]);
                    }
                }
            }
            else
            {
                // Is the method being specified in the route?
                if (strpos($this->default_controller, '/') !== FALSE)
                {
                    $x = explode('/', $this->default_controller);

                    $this->set_class($x[0]);
                    $this->set_method($x[1]);
                }
                else
                {
                    $this->set_class($this->default_controller);
                    $this->set_method('index');
                }

                // Does the default controller exist in the sub-folder?
                if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php'))
                {
                    $this->directory = '';
                    return array();
                }

            }

            return $segments;
        }


        // If we've gotten this far it means that the URI does not correlate to a valid
        // controller class.  We will now see if there is an override
        if ( ! empty($this->routes['404_override']))
        {
            $x = explode('/', $this->routes['404_override']);

            $this->set_class($x[0]);
            $this->set_method(isset($x[1]) ? $x[1] : 'index');

            return $x;
        }


        // Nothing else to do at this point but show a 404
        show_404($segments[0]);
    }

    /**
     * @return mixed
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public function getAction() {
        return $this->action;
    }

}
