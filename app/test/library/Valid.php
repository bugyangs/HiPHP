<?php
/**
 * @name Library_Valid
 * @desc 提供公用合法性验证类并返回标准化结果
 * @date   2016-10-11
 */
class Library_Valid
{
    /**
     *
     */
    const PROCESSTYPE_THROW = 1; //抛异常
    /**
     *
     */
    const PROCESSTYPE_IGNORE = 2; //忽略，继续往后走

    /*
     * refer 验证 baidu 域
     */
    /**
     *
     */
    const MATH_BASE_BAIDU_DOMAIN = "/^([a-zA-Z0-9\-]{0,16}\.){0,3}baidu\.com$/";

    /*
     * 日期 验证
     */
    /**
     *
     */
    const MATH_BASE_DATE = "/^\d{4}-\d{2}-\d{2}$/";

    /**
     * 业务参数获取
     * @param array $data          装载需要检验的数组
     * @param array $schema = array(
     *                           $k=>array(
     *                               'func'=>'',
     *                               'defaultV'=>'', //不设置此值表示校验结果为false时抛异常
     *                           )
     *                       )
     * @return array
     */
    public static function getFormattedInputs($data, $schema)
    {
        $ret = array();
        foreach($schema as $k => $v)
        {
            $input = array();
            if(!isset($v['func']))
            {
                continue;
            }
            $input[] = $v['func'];
            $input[] = $k;
            $input[] = isset($data[$k]) ? $data[$k] : null;

            $processType = self::PROCESSTYPE_IGNORE;
            if(!isset($v['defaultV']))
            {
                $processType = self::PROCESSTYPE_THROW;
            }
            $input[] = $processType;

            $tmpRet = self::check($input);

            $ret[$k] = (($tmpRet === false) ? $v['defaultV'] : $tmpRet);
        }

        return $ret;
    }


    /**
     * 统一调用处理入口，当然后面各方法可以被单独调用
     * @return mixed
     * @throws Exception
     */
    public static function check()
    {
        $numArgs = func_num_args();
        $argList = func_get_args();

        if($numArgs < 1 || !isset($argList[0]))
        {
            throw new Exception('error params:' . __CLASS__ . '::' . __FUNCTION__ . '(' . implode(',', $argList[0]) . ')');
        }
        else
        {
            $argv = $argList[0];
            return self::$argv[0]($argv[1], $argv[2], $argv[3]);
        }
    }

    /**
     * @param $name
     * @param $argvs
     *
     * @throws Exception
     */
    public function __call($name, $argvs)
    {
        throw new Exception('undefined method:' . __CLASS__ . '::' . $name . '(' . implode(',', $argvs) . ')');
	}

	/**
	 * @note : 性别验证
	 * @param key
	 * @param value
	 * @return 0女1男
	 */

	public static function isSex($key, $value, $processType = self::PROCESSTYPE_IGNORE) {
		$pattern = '/^(0|1)$/';

		if (!preg_match($pattern, $value)) {
			self::_processAbnormal($processType, $key . ' 格式不正确:' . $value);
			return false;
		}

		return intval($value);
	}

    /**
     * 数字校验
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isNumber($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $pattern = '/^[0-9]+$/';
        if(!preg_match($pattern, $value))
        {
            self::_processAbnormal($processType, $key . ' 格式不正确:' . $value);

            return false;
        }

        return intval($value);
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|float
     * @throws Exception
     */
    public static function isFloat($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $pattern = '/^[0-9\.\-]+$/';
        if(!preg_match($pattern, $value))
        {
            self::_processAbnormal($processType, $key . ' 格式不正确:' . $value);

            return false;
        }

        return floatval($value);
    }

    /**
     *  ip校验
     *
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isIp($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $pattern = '/^[0-9.]+$/';
        if(!preg_match($pattern, $value))
        {
            self::_processAbnormal($processType, $key . ' 格式不正确:' . $value);

            return false;
        }

        return $value;
    }

    /**
     * 字符串校验
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isString($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        //未设置时值为null,trim之后为''
        if($value != null)
        {
            $value = trim($value);
        }

        if(!is_string($value))
        {
            self::_processAbnormal($processType, $key . '不能为空');

            return false;
        }

        return Library_String::filter($value);
    }
    /**
     * 字符串校验-不进行过滤
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isStringNoFilter($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        //未设置时值为null,trim之后为''
        if($value != null)
        {
            $value = trim($value);
        }

        if(!is_string($value))
        {
            self::_processAbnormal($processType, $key . '不能为空');

            return false;
        }

        return $value;
    }

    /**
     * 手机号格式验证
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isCellPhone($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(empty($value))
        {
            self::_processAbnormal($processType, '请输入正确的手机号');


            return false;
        }

        $pattern = '/^(13[0-9]|14[0-9]|15[0-35-9]|17[0-35-9]|18[0-9])[0-9]{8}$/';
        if(!preg_match($pattern, $value))
        {
            self::_processAbnormal($processType, '请输入正确的手机号');

            return false;
        }

        return $value;
    }

    /**
     * 邮箱格式验证
     *
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return mixed
     * @throws Exception
     */
    public static function isEmail($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            self::_processAbnormal($processType, '请输入正确的邮箱地址');
        }
        return $value;
    }

    /**
     *  6位密码格式验证
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isPassword($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $pattern = '/^[0-9]{6}$/';
        if(!preg_match($pattern, $value))
        {
            self::_processAbnormal($processType, '密码格式不正确:' . $value);

            return false;
        }

        return $value;
    }


    /**
     * query检测
     *
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|string
     */
    public static function isQuery($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        return self::isLen($key, $value, 1, 100, $processType);
    }


    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|string
     * @throws Exception
     */
    public static function isExist($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $value = trim($value);
        if(!isset($value))
        {
            self::_processAbnormal($processType, $key . '不能为空');

            return false;
        }

        return strip_tags($value);
    }


    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|string
     * @throws Exception
     */
    public static function isSort($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $value = strtoupper(trim($value));
        if(!in_array($value, array('ASC', 'DESC')))
        {
            self::_processAbnormal($processType, $key . '不合法：' . $value);

            return false;
        }

        return $value;
    }

    /**
     * 页码校验,页码需默认1-99
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|int
     */
    public static function isPage($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $value = self::isNumber($key, $value, $processType);
        if(!$value)
        {
            return false;
        }
        $value = intval($value);

        return ($value > 0 && $value < 100) ? $value : 1;
    }

    /**
     * 每页数量限制，上限为50
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool|int
     */
    public static function isPageSize($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        $value = self::isNumber($key, $value, $processType);
        if(!$value)
        {
            return false;
        }
        $value = intval($value);

        return ($value > 0 && $value < 50) ? $value : 10;
    }


    /**
     * 字符长度判断
     * @param     $key
     * @param     $value
     * @param int $min
     * @param int $max
     * @param int $processType
     *
     * @return bool|string
     */
    public static function isLen($key, $value, $min = 0, $max = 0, $processType = self::PROCESSTYPE_IGNORE)
    {
        $value = trim($value);
        $len   = mb_strlen($value, "utf8");

        if($min && ($len < $min))
        {
            self::_processAbnormal($processType, $key . ' 长度为:' . $len . ' 小于最小限制:' . $min);

            return false;
        }

        if($max && ($len > $max))
        {
            self::_processAbnormal($processType, $key . ' 长度为:' . $len . ' 大于最大限制:' . $max);

            return false;
        }

        return strip_tags($value);
    }

    /*
     * 对出错时的处理
     */
    /**
     * @param     $processType
     * @param     $message
     * @param int $code
     *
     * @return bool
     * @throws Exception
     */
    private static function _processAbnormal($processType, $message) {
        if($processType == self::PROCESSTYPE_THROW) {
            throw new Library_Exception(Library_Exception::Library_CUSTOM_PARAM_VALID, $message);
        }

        return true;
    }

    /*
     * 判断是否 baidu.com 域名
     * @param string $domain
     * @return boolean
     */
    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     * @throws Exception
     */
    public static function isBaiduDomain($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(preg_match(self::MATH_BASE_BAIDU_DOMAIN, $value))
        {
            return true;
        }

        self::_processAbnormal($processType, $key . ' 不是合法的百度域： ' . $value);

        return false;
    }

    /*
     * 判断是否是合法的sid
     * @param string $sid
     * @return boolean
     */
    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     * @throws Exception
     */
    public static function isSid($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(empty($value))
        {
            self::_processAbnormal($processType, 'sid不合法： ' . $value);

            return false;
        }
        $sidArr = explode("&", $value);
        if(empty($sidArr))
        {
            self::_processAbnormal($processType, 'sid不合法： ' . $value);

            return false;
        }
        foreach($sidArr as $item)
        {
            $parts = explode(":", $item);
            if(count($parts) != 2)
            {
                self::_processAbnormal($processType, 'sid不合法： ' . $value);

                return false;
            }
        }

        return true;
    }


    /**
     * 验证是否是日期
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isDate($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(preg_match(self::MATH_BASE_DATE, $value))
        {
            return true;
        }

        self::_processAbnormal($processType, $key . ' 不是合法的日期： ' . $value);

        return false;
    }

    /**
     * @param     $key
     * @param string $value       日期
     * @param string $format      待检查的格式
     * @param int $processType 异常类型
     *
     * @return bool 返回是否符合格式要求
     */
    public static function isDateAsFormat($key, $value, $format, $processType = self::PROCESSTYPE_IGNORE)
    {
        $date2TimeStr = strtotime($value);
        if($date2TimeStr && date($format, $date2TimeStr) == $value)
        {
            return true;
        }
        self::_processAbnormal($processType, $key . ' 不是合法的符合格式： ' . $format . ' 日期： ' . $value);

        return false;
    }

    /**
     * 验证身份证号码
     * @param     $key
     * @param     $idNumber
     * @param int $processType
     *
     * @return bool
     */
    public static function isIdNumber($key, $idNumber, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(!preg_match('/^\d{17}[0-9xX]$/', $idNumber)) {
            self::_processAbnormal($processType, '请填写正确的身份证号');
            return false;
        }

        $year = intval(substr($idNumber, 6, 4));
        $month = intval(substr($idNumber, 10, 2));
        $day = intval(substr($idNumber, 12, 2));

        if (!checkdate($month, $day, $year)) {
            self::_processAbnormal($processType, '请填写正确的身份证号');
            return false;
        }

        $idNumberBase = substr($idNumber, 0, 17);
        //加权因子 
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值 
        $verifyNumbers = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum      = 0;
        for($i = 0; $i < strlen($idNumberBase); $i++)
        {
            $checksum += intval(substr($idNumberBase, $i, 1)) * $factor[$i];
        }
        $mod          = $checksum % 11;
        $verifyNumber = $verifyNumbers[$mod];
        $lastNum      = strtoupper(substr($idNumber, 17, 1));

        if($verifyNumber == $lastNum)
        {
            return $idNumber;
        }
        else
        {
            self::_processAbnormal($processType, '请填写正确的身份证号');

            return false;
        }
    }

    /**
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return mixed
     * @throws Exception
     */
    public static function isUrl($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
//        if(preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $value))
        if(preg_match("/^(http|https):\/\/[A-Za-z0-9-]+\.[A-Za-z0-9-]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $value))
        {
            return $value;
        }
        else
        {
            self::_processAbnormal($processType, $key . '不合法： ' . $value);
        }
    }

    /**
     * 中文姓名校验
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isChineseName($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        //未设置时值为null,trim之后为''
        if($value != null)
        {
            $value = trim($value);
        }

        $pattern = "/^[\x7f-\xff]+[\.]?[\x7f-\xff]+$/";
        if (!preg_match($pattern, $value)) {
            self::_processAbnormal($processType, $key . '不合法');
            return false;
        }
        $len = mb_strlen($value, "utf8");
        if ($len < 2 || $len >20){
            self::_processAbnormal($processType, $key . '不合法');
            return false;
        }

        return Library_String::filter($value);
    }
    
    /**
     * 数组校验
     * @param     $key
     * @param     $value
     * @param int $processType
     *
     * @return bool
     */
    public static function isArray($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        
        if(!is_array($value))
        {
            self::_processAbnormal($processType, $key . ' 格式不正确');

            return false;
        }

        return $value;
    }

    /**
     * 判断是否是合法的就诊卡号（只有字母和数字且必须包含数字）
     *
     * @param $key
     * @param $value
     * @param int $processType
     * @return bool
     * @throws Exception
     */
    public static function isVisitCard($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if(empty($value) && $value !== 0 && $value !== '0')
        {
            self::_processAbnormal($processType, '就诊卡不能为空');
            return false;
        }

        return Library_String::filter($value);
    }

    /**
     * 判断是否是合法的卡号
     *
     * @param $key
     * @param $value
     * @param int $processType
     * @return bool
     * @throws Exception
     */
    public static function isCardNumber($key, $value, $processType = self::PROCESSTYPE_IGNORE)
    {
        if (empty($value))
        {
            self::_processAbnormal($processType, '卡号不能为空');
            return false;   
        }
        //必须是字母和数字
        if(!ctype_alnum($value))
        {
            self::_processAbnormal($processType, '卡号格式错误');
            return false;
        }

        return $value;
    }

    /**
     * 判断是否是合法的就诊卡密码
     *
     * @param $key
     * @param $value
     * @param int $processType
     * @return bool
     * @throws Exception
     */
    public static function isVisitCardPassword($key, $value, $processType=self::PROCESSTYPE_IGNORE)
    {
        $pat = '/^[a-zA-Z0-9+\/=]+$/';
        if (!preg_match($pat, $value)) {
            self::_processAbnormal(self::PROCESSTYPE_THROW, "就诊卡密码错误");
            return false;
        }
        return $value;
    }

    /**
     * 判断是否为md5值
     *
     * @param $key
     * @param $value
     * @param int $processType
     * @return bool
     * @throws Exception
     */
    public static function isMd5($key, $value, $processType=self::PROCESSTYPE_IGNORE)
    {
        $pat = '/^[a-f0-9]{32}$/';
        if (!preg_match($pat, $value)) {
            return false;
        }
        return $value;
    }
}

