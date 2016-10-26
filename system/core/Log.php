<?php
/*********************************************
 * format string 格式，取自lighttpd文档
 * 前面标记 - 代表ODP的Log库不支持
 * 行为不一致的，均有注释说明
 * 后面的 === 之后的，是ODP Log库扩展的功能
  ====== ================================
  Option Description
  ====== ================================
  %%     a percent sign
  %h     name or address of remote-host
  -%l     ident name (not supported)
  -%u     authenticated user
  %t     timestamp of the end-time of the request //param, show current time, param specifies strftime format
  -%r     request-line 
  -%s     status code 
  -%b     bytes sent for the body
  %i     HTTP-header field //param
  %a     remote address
  %A     local address
  -%B     same as %b
  %C     cookie field (not supported) //param
  %D     time used in ms
  %e     environment variable //param
  %f     physical filename
  %H     request protocol (HTTP/1.0, ...)
  %m     request method (GET, POST, ...)
  -%n     (not supported)
  -%o     `response header`_
  %p     server port
  -%P     (not supported)
  %q     query string
  %T     time used in seconds //support param, s, ms, us, default to s
  %U     request URL
  %v     server-name
  %V     HTTP request host name
  -%X     connection status
  -%I     bytes incomming
  -%O     bytes outgoing
  ====== ================================
  %L     Log level
  %N     line number
  %E     err_no
  %l     log_id
  %u     user
  %S     strArray, support param, takes a key and removes the key from %S
  %M     error message

  %x     ODP extension, supports various param, like log_level, line_number etc.

  currently supported param for %x:
  log_level, line, class, function, err_no, err_msg, log_id, app, function_param, argv, encoded_str_array

  in %x, prepend u_ to key to urlencode before its value
*************************************************/
class Core_Log
{
    const LOG_LEVEL_FATAL   = 0x01;
    const LOG_LEVEL_WARNING = 0x02;
    const LOG_LEVEL_NOTICE  = 0x04;
    const LOG_LEVEL_TRACE   = 0x08;
    const LOG_LEVEL_DEBUG   = 0x10;
    
    const LOG_OMP_AND_NORMAL = 0;
    const LOG_OMP_ONLY = 1;
    const LOG_NORMAL_ONLY = 2;

    public static $arrLogLevels = array(
        self::LOG_LEVEL_FATAL   => 'FATAL',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_NOTICE  => 'NOTICE',
        self::LOG_LEVEL_TRACE    => 'TRACE',
        self::LOG_LEVEL_DEBUG   => 'DEBUG',
    );

    protected $intLevel;
    protected $strLogFile;
    protected $bolAutoRotate;
    protected $addNotice = array();
    protected $pbAddNotice = array();
    protected $objWriter = null;
    
    protected $confPblog = array();

    private static $arrInstance = array();
    public static $current_instance;
    private $current_args;

    private static $intWritePbLog = null;
	private static $bolIsOmp    = null;
	private static $strLogPath  = null;
	private static $strDataPath = null;
	
	private static $lastLogs=array();
	private static $lastLogSize=0;
	private static $logWriters=array();

    const DEFAULT_FORMAT = '%L: %t [%f:%N] errno[%E] logId[%l] uri[%U] user[%u] refer[%{referer}i] cookie[%{cookie}i] %M';
    const DEFAULT_FORMAT_STD = '%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x]';
    const DEFAULT_FORMAT_STD_DETAIL = '%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x cookie=%{u_cookie}x]';
	// TRACING 打印APP日志格式  增加 module ,spanid ,force_sampling
	const FORMAT_TRACING_FROM = 'logId[%l]';
    const FORMAT_TRACING_TO = 'logId[%l] module[%{app}x]';

    const DEFAULT_FORMAT_DEBUG = '%L: %t [%f:%N] errno[%E] logId[%l] %M';
    const DEFAULT_FORMAT_FRAMEWORK = '%L: %{%m-%d %H:%M:%S}t [%f:%N] errno=%E logId=%l %M';
    
    private function __construct($arrLogConfig)
    {
        $this->intLevel         = $arrLogConfig['level'];
        $this->bolAutoRotate    = $arrLogConfig['auto_rotate'];
        $this->strLogFile       = $arrLogConfig['log_file'];
        $this->strFormat        = $arrLogConfig['format']; 
        $this->strFormatWF      = $arrLogConfig['format_wf'];

        $this->objWriter = new Core_Log_File($arrLogConfig['log_file'] , Core_Log_File::WRITER_BUF );
	}

    /**
     * 日志的前缀为AppName
     * @return string
     */
	public static function getLogPrefix() {
		return APP_NAME;
	}

    /**
     * 日志打印的根目录
     * @return string
     */
	public static function getLogPath() {
        return LOG_PATH;
	}

    // 获取指定App的log对象，默认为当前App
    /**
     * 
     * @return Core_Log
     * */
    public static function getInstance($app = null) {
        if(empty($app)) {
			$app = self::getLogPrefix();
        }
        if(empty(self::$arrInstance[$app])) {
            $appConf = Core_Config::getInstance("Log")->item();
            // 生成路径
			$logPath = self::getLogPath();
            $logFile = $logPath. "/" . APP_NAME ."/$app.log";
            //get log format
            if (isset($appConf['format'])) {
                $format = $appConf['format'];
            } else {
                $format = self::DEFAULT_FORMAT;
            }
            $format = str_replace(self::FORMAT_TRACING_FROM, self::FORMAT_TRACING_TO, $format);
            $logConf = array(
                'level'         => intval($appConf['level']),
                'auto_rotate'   => ($appConf['auto_rotate'] == '1'),
                'log_file'      => $logFile,
                'format'        => $format,
                'format_wf'     => $format,
            );
            self::$arrInstance[$app] = new Core_Log($logConf);
        }
        return self::$arrInstance[$app];
    }

    /**
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return mixed
     */
    public static function debug($str, $errno = 0, $arrArgs = null, $depth = 0) {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $depth + 1, '', self::DEFAULT_FORMAT_DEBUG);
		return $ret;
	}

    /**
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return mixed
     */
	public static function trace($str, $errno = 0, $arrArgs = null, $depth = 0) {
	 		$ret = self::getInstance()->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $depth + 1, '', self::DEFAULT_FORMAT_DEBUG);
		return $ret;
	}

	public static function notice($str, $errno = 0, $arrArgs = null, $depth = 0) {
			$ret = self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $depth + 1);
	}

    /**
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     */
	public static function warning($str, $errno = 0, $arrArgs = null, $depth = 0) {
			$ret = self::getInstance()->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $depth + 1);
	}

    /**
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     */
	public static function fatal($str, $errno = 0, $arrArgs = null, $depth = 0) {
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $depth + 1);
	}

    /**
     * @desc 设置某些log信息
     */
    private function setCurLog($intLevel, $str, $errno = 0, $depth = 0) {
    	//assign data required
    	$this->current_log_level = self::$arrLogLevels[$intLevel];
    	$this->current_err_no = $errno;
    	$this->current_err_msg = $str;
    	// 不调用 args，减少内存消耗
    	if( defined('DEBUG_BACKTRACE_IGNORE_ARGS') ){
    		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS , $depth + 2);
    	}
    	else{
    		$trace = debug_backtrace();
    	}
    	$depth2 = $depth + 1;
    	if( $depth >= count($trace) )
    	{
    		$depth = count($trace) - 1;
    		$depth2 = $depth;
    	}
    	$this->current_file = isset( $trace[$depth2]['file'] )
    	? $trace[$depth2]['file'] : "" ;
    	$this->current_line = isset( $trace[$depth2]['line'] )
    	? $trace[$depth2]['line'] : "";
    	$this->current_function = isset( $trace[$depth2]['function'] )
    	? $trace[$depth2]['function'] : "";
    	$this->current_class = isset( $trace[$depth2]['class'] )
    	? $trace[$depth2]['class'] : "" ;
    	$this->current_function_param = isset( $trace[$depth2]['args'] )
    	? $trace[$depth2]['args'] : "";
    	self::$current_instance = $this;
    }

    private function writeLog($intLevel, $str, $errno = 0, $depth = 0, $filename_suffix = '', $log_format = null)
    {
        if( $intLevel > $this->intLevel 
        	|| !isset(self::$arrLogLevels[$intLevel]) 
    	){
            return false;
        }

        //log file name
        $strLogFile = $this->strLogFile;
        if( ($intLevel & self::LOG_LEVEL_WARNING) 
        	|| ($intLevel & self::LOG_LEVEL_FATAL) 
    	){
            $strLogFile .= '.wf';
        }

        $strLogFile .= $filename_suffix;

        $this->setCurLog($intLevel, $str, $errno , $depth);

        //get the format
        if ($log_format === null){
            $format = $this->getFormat($intLevel);
        }
        else{
            $format = $log_format;
        }
        $str = $this->getLogString($format);

        // 日志文件加上年月日配置
        if($this->bolAutoRotate){
            $strLogFile .= '.'.date('YmdH');
        }

        // 保留最近N条日志，不知道有啥用
        if(self::$lastLogSize > 0)
        {
            self::$lastLogs[] = $str;
            if(count(self::$lastLogs) > self::$lastLogSize)
            {
                array_shift(self::$lastLogs);
            }
        }
        
        $options = array(
            'arrLogFile' => array(
                $strLogFile => $strLogFile ,    
            ),   
        );   
        if(PHP_SAPI == 'cli'){
            $options['strWriterHandler'] = Core_Log_File::WRITER_NOBUF;  
        }
        // 使用缓存writer进行打印
        $this->objWriter->setOptions($options);

        return $this->objWriter->log($str , $strLogFile);
    }

    private function getFormat($level) {
        if ($level == self::LOG_LEVEL_FATAL || $level == self::LOG_LEVEL_WARNING) {
            $fmtstr = $this->strFormatWF;
        } else {
            $fmtstr = $this->strFormat;
        }
        return $fmtstr;
    }

    /**
     * @param $format
     * @return mixed
     */
    public function getLogString($format) {
        $md5val = md5($format);
        $func = "_core_log_$md5val";
        if (function_exists($func)) {
            return $func();
        }
        $dataPath = self::getDataPath();
        $filename = $dataPath . '/log/'.$md5val.'.php';
        if (!file_exists($filename)) {
            $tmp_filename = $filename . '.' . posix_getpid() . '.' . rand();

            if(!is_dir($dataPath . '/log')) {
                @mkdir($dataPath . '/log');
            }
            file_put_contents($tmp_filename, $this->parseFormat($format));
            rename($tmp_filename, $filename);
        }
        include_once($filename);
        $str = $func();
        return $str;
    }

    /**
     * @param $format
     * @return string
     */
    public function parseFormat($format) {
        $matches = array();
        $regex = '/%(?:{([^}]*)})?(.)/';
        preg_match_all($regex, $format, $matches);
        $prelim = array();
        $action = array();
        $prelim_done = array();

        $len = count($matches[0]);
        for($i = 0; $i < $len; $i++) {
            $code = $matches[2][$i];
            $param = $matches[1][$i];
            switch($code) {
                case 'h':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Core_Log::getClientIp())";
                    break;
                case 't':
                    $action[] = ($param == '')? "strftime('%y-%m-%d %H:%M:%S')" : "strftime(" . var_export($param, true) . ")";
                    break;
                case 'i':
                    $key = 'HTTP_' . str_replace('-', '_', strtoupper($param));
                    $key = var_export($key, true);
                    $action[] = "(isset(\$_SERVER[$key])? \$_SERVER[$key] : '')";
                    break;
                case 'a':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Core_Log::getClientIp())";
                    break;
                case 'A':
                    $action[] = "(isset(\$_SERVER['SERVER_ADDR'])? \$_SERVER['SERVER_ADDR'] : '')";
                    break;
                case 'C':
                    if ($param == '') {
                        $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                    } else {
                        $param = var_export($param, true);
                        $action[] = "(isset(\$_COOKIE[$param])? \$_COOKIE[$param] : '')";
                    }
                    break;
                case 'D':
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                    break;
                case 'e':
                    $param = var_export($param, true);
                    $action[] = "((getenv($param) !== false)? getenv($param) : '')";
                    break;
                case 'f':
                    $action[] = 'Core_Log::$current_instance->current_file';
                    break;
                case 'H':
                    $action[] = "(isset(\$_SERVER['SERVER_PROTOCOL'])? \$_SERVER['SERVER_PROTOCOL'] : '')";
                    break;
                case 'm':
                    $action[] = "(isset(\$_SERVER['REQUEST_METHOD'])? \$_SERVER['REQUEST_METHOD'] : '')";
                    break;
                case 'p':
                    $action[] = "(isset(\$_SERVER['SERVER_PORT'])? \$_SERVER['SERVER_PORT'] : '')";
                    break;
                case 'q':
                    $action[] = "(isset(\$_SERVER['QUERY_STRING'])? \$_SERVER['QUERY_STRING'] : '')";
                    break;
                case 'T':
                    switch($param) {
                        case 'ms':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                            break;
                        case 'us':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000000 - REQUEST_TIME_US) : '')";
                            break;
                        default:
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) - REQUEST_TIME_US/1000000) : '')";
                    }
                    break;
                case 'U':
                    $action[] = "(isset(\$_SERVER['REQUEST_URI'])? \$_SERVER['REQUEST_URI'] : '')";
                    break;
                case 'v':
                    $action[] = "(isset(\$_SERVER['HOSTNAME'])? \$_SERVER['HOSTNAME'] : '')";
                    break;
                case 'V':
                    $action[] = "(isset(\$_SERVER['HTTP_HOST'])? \$_SERVER['HTTP_HOST'] : '')";
                    break;

                case 'L':
                    $action[] = 'Core_Log::$current_instance->current_log_level';
                    break;
                case 'N':
                    $action[] = 'Core_Log::$current_instance->current_line';
                    break;
                case 'E':
                    $action[] = 'Core_Log::$current_instance->current_err_no';
                    break;
                case 'l':
                    $action[] = "Core_Log::genLogID()";
                    break;
                case 'u':
                    if (!isset($prelim_done['user'])) {
                        $prelim[] = '$____user____ = Core_Passport::getUserInfoFromCookie();';
                        $prelim_done['user'] = true;
                    }
                    $action[] = "((defined('CLIENT_IP') ? CLIENT_IP: Core_Log::getClientIp()) . ' ' . \$____user____['uid'] . ' ' . \$____user____['uname'])";
                    break;
                case 'M':
                    $action[] = 'Core_Log::$current_instance->current_err_msg';
                    break;
                case 'x':
                    $need_urlencode = false;
                    if (substr($param, 0, 2) == 'u_') {
                        $need_urlencode = true;
                        $param = substr($param, 2);
                    }
                    switch($param) {
                        case 'log_level':
                        case 'line':
                        case 'class':
                        case 'function':
                        case 'err_no':
                        case 'err_msg':
                            $action[] = 'Core_Log::$current_instance->current_'.$param;
                            break;
                        case 'log_id':
                            $action[] = "Core_Log::genLogID()";
                            break;
                        case 'app':
                            $action[] = "Core_Log::getLogPrefix()";
                            break;
                        case 'function_param':
                            $action[] = 'Core_Log::flattenArgs(Core_Log::$current_instance->current_function_param)';
                            break;
                        case 'argv':
                            $action[] = '(isset($GLOBALS["argv"])? Core_Log::flattenArgs($GLOBALS["argv"]) : \'\')';
                            break;
                        case 'pid':
                            $action[] = 'posix_getpid()';
                            break;
                        case 'encoded_str_array':
                            $action[] = 'Core_Log::$current_instance->get_str_args_std()';
                            break;
                        case 'cookie':
                            $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                            break;
                        default:
                            $action[] = "''";
                    }
                    if ($need_urlencode) {
                        $action_len = count($action);
                        $action[$action_len-1] = 'rawurlencode(' . $action[$action_len-1] . ')';
                    }
                    break;
                case '%':
                    $action[] =  "'%'";
                    break;
                default:
                    $action[] = "''";
            }
        }

        $strformat = preg_split($regex, $format);
        $code = var_export($strformat[0], true);
        for($i = 1; $i < count($strformat); $i++) {
            $code = $code . ' . ' . $action[$i-1] . ' . ' . var_export($strformat[$i], true);
        }
        $code .=  ' . "\n"';
        $pre = implode("\n", $prelim);

        $cmt = "Used for app " . self::getLogPrefix() . "\n";
        $cmt .= "Original format string: " . str_replace('*/', '* /', $format);

        $md5val = md5($format);
        $func = "_core_log_$md5val";
        $str = "<?php \n/*\n$cmt\n*/\nfunction $func() {\n$pre\nreturn $code;\n}";
        return $str;
    }

    /**
     * @return string
     */
    public static function getDataPath(){
        return DATA_PATH;
    }

    /**
     * 获取客户端ip
     * @return string
     */
    public static function getClientIp()
    {
        $uip = '';
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')) {
            $uip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            strpos($uip, ',') && list($uip) = explode(',', $uip);
        } else if(!empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown')) {
            $uip = $_SERVER['HTTP_CLIENT_IP'];
        } else if(!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $uip = $_SERVER['REMOTE_ADDR'];
        }
        return $uip;
    }

    // 生成logid
    public static function genLogID() {
        if(defined('LOG_ID')){
            return LOG_ID;
        }
        $arr = gettimeofday();
        $logId = ((($arr['sec']*100000 + $arr['usec']/10) & 0x7FFFFFFF) | 0x80000000);
        define('LOG_ID', $logId);
        return LOG_ID;
    }
}
