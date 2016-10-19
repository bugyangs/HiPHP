<?php
/**
 * @file   Error.php
 * @author zhangyongfei
 */
class Controller_Error extends Library_ControllerBase
{

    /**
     * @param $exception
     */
    public function errorAction($exception)
    {
//        header("Status: 404 Not Found");
        $exceptionInfo = array(
            'type' => get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        );
        var_dump($exceptionInfo);die;

    }
}