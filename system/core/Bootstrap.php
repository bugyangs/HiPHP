<?php

define("QP_VERSION", "1.0.0");
require_once __DIR__ . "/Init.php";
class Core_Bootstrap {
    private static $instance = null;
    private $appName;
    /*
     * @var Core_Route
     */
    private $route;

    /**
     * @param $appName
     * @return null|Core_Bootstrap
     */
    public static function instance($appName) {
        if(!self::$instance) {
            self::$instance = new self($appName);
        }
        return self::$instance;
    }

    /**
     * @param $appName
     */
    private function __construct($appName) {
        $this->appName = $appName;
        set_exception_handler(array($this, "handleExceptions"));
    }

    /**
     *
     */
    public function run() {
        Core_Init::instance($this->appName);
        $this->route = Core_Route::getInstance();
        $this->execute();
    }

    /**
     * @param $class
     * @param $method
     * @throws Core_Exception
     */
    private function execute() {
        $controllerClass = $this->route->fetchControllerClass();
        $actionClass = $this->route->fetchActionClass();
        if (!class_exists($controllerClass)) {
            throw new Core_Exception(Core_Exception::SYS_UNKNOWN_ROUTE);
        }
        $controller = new $controllerClass();
        if(!array_key_exists($this->route->action, $controller->actions)) {
            throw new Core_Exception(Core_Exception::SYS_UNKNOWN_ROUTE);
        }
        $actionPath = ROOT_PATH . "/app/". APP_NAME . "/" . $controller->actions[$this->route->action];
        require_once $actionPath;
        $action = new $actionClass();
        call_user_func(array($action, "execute"));
    }

    /**
     * @param $exception
     */
    public function handleExceptions($exception) {
        if(class_exists("Controller_Error")) {
            $errorController = new Controller_Error();
            if(method_exists($errorController, "errorAction")) {
                $errorController->errorAction($exception);
            }
        }
    }

}