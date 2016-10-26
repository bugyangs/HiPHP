<?php
/**
 * @name Core_String
 * @desc 字符串处理工具类，包括字符截断、飘红等功能
 * @date   2016-10-11
 */
class Core_String
{
    /**
     * 过滤字符
     *
     * @param       $key
     * @param array $filterArray
     *
     * @return mixed
     */
    public static function filter($key, $filterArray = array())
    {
        $key             = rawurldecode(trim($key));
        $shouldNotExists = array("/", "\\", "<", ">", ")", "(", "[", "]", "\"", "'", "\r", "\n", "\0");
        //增加一个参数，可以过滤掉自定义的字符
        if(!empty($filterArray))
        {
            $shouldNotExists = array_merge($shouldNotExists, $filterArray);
        }
        $keyword = str_replace($shouldNotExists, "", $key);

        return $keyword;
    }

    /**
     * 过滤字符高级版
     *
     * @param       $key
     * @param array $filterArray
     * @param array $excludeArray
     *
     * @return mixed
     */
    public static function filterEx($key, $filterArray = array(), $excludeArray = array())
    {
        $key             = rawurldecode(trim($key));
        $shouldNotExists = array("/", "\\", "<", ">", ")", "(", "[", "]", "\"", "'", "\r", "\n", "\0");
        //增加一个参数，可以过滤掉自定义的字符
        if(!empty($filterArray))
        {
            $shouldNotExists = array_merge($shouldNotExists, $filterArray);
        }
        //考虑不过滤的字符
        $shouldNotExists = array_diff($shouldNotExists, $excludeArray);
        $keyword = str_replace($shouldNotExists, "", $key);

        return $keyword;
    }

    /**
     * 字符串截断，根据宽度截断，可以理解为截断多少个汉字
     *
     * @param string $str 原始字符串
     * @param int    $len 截断长度（字数）
     *
     * @return string 截断后的字符串
     */
    public static function cutByCharNum($str, $len)
    {
        $len = $len * 2;

        return mb_strimwidth($str, 0, $len, '...', 'utf-8');
    }


    /**
     * 字符串截断，根据宽度截断，一个汉字宽度为2，英文字母宽度为1，默认在末尾增加...
     *
     * @param string $str     原始字符串
     * @param int    $len     截断长度(字符数)
     * @param string $addChar 在...后面增加的字符
     *
     * @return string 截断后的字符串
     */
    public static function cutByChar($str, $len, $addChar = "")
    {
        return mb_strimwidth($str, 0, $len, '...' . $addChar, 'utf-8');
    }

    /**
     * 字符串截断，根据宽度截断，一个汉字宽度为2，英文字母宽度为1，
     *
     * @param string $str     原始字符串
     * @param int    $len     截断长度(字符数)
     * @param string $addString 在 截断后的字符串后面增加的字符
     *
     * @return string 截断后的字符串
     */
    public static function cutString($str, $len, $addString = "...")
    {
        return mb_strimwidth($str, 0, $len, $addString, 'utf-8');
    }
    
    /**
     * utf-8 字符串（字母、数字、汉字组合）截取函数 edit by liweibing
     * @param string $sourcestr：要截取的字符串，默认空
     * @param int $i：开始截取地方，默认0
     * @param int $cutlength：截取长度（文字个数），默认100
     * @param string $endstr：截取后的字符串末尾字符串，默认是 “….”
     * @param bool $letterSpace 字母、数字是占用一个字符，还是半个字符。true：一个字符.false:半个字符
     * @return string 截取后的字符串
     */
    public static function getSubstring($sourceStr = '', $i = 0, $cutLength = 100, $endStr = '...', $letterSpace = false) {
        $strLength = strlen($sourceStr); // 字符串的字节数
        $n = 0;
        $returnStr = "";
        while (($n < $cutLength) && ( $i <= $strLength))
        {
            $temp_str = substr($sourceStr, $i, 1);
            $ascNum = Ord($temp_str); // ascii码
            if ($ascNum >= 224)
            {
                $returnStr = $returnStr . substr($sourceStr, $i, 3);
                $i = $i + 3;
                $n ++;
            }
            elseif ($ascNum >= 192)
            {
                $returnStr = $returnStr . substr($sourceStr, $i, 2);
                $i = $i + 2;
                $n ++;
            }
            elseif ($letterSpace === true && ($ascNum >= 32 && $ascNum <= 127))
            {
                $returnStr = $returnStr . substr($sourceStr, $i, 1);
                $i = $i + 1;
                $n++;
            }
            else
            {

                $returnStr = $returnStr . substr($sourceStr, $i, 1);
                $i = $i + 1;
                $n = $n + 0.5;
            }
        }
        if ($i < $strLength) {
            $returnStr .= $endStr;
        }
        return $returnStr;
    }

    /**
     * 隐藏用户名；输入：黄晓明，返回：黄*明
     *
     * @param $nameString
     *
     * @return string
     */
    public static function hideName($nameString, $hideAllRightName = false)
    {
        $result     = '';
        $nameString = trim($nameString);
        $length     = mb_strlen($nameString, 'utf-8');
        if(!$hideAllRightName) {
            if($length == 2)
            {
                $result .= mb_substr($nameString, 0, 1, 'utf-8') . str_repeat('*', 3);
            }
            else if($length > 2) {
                $result .= mb_substr($nameString, 0, 1, 'utf-8') . str_repeat('*', 3). mb_substr($nameString, -1, 1, 'utf-8');
            }
        }
        else {
            if($length >= 2)
            {
                $result .= mb_substr($nameString, 0, 1, 'utf-8') . str_repeat('*', 3);
            }
            else {
                $result = "***";
            }
        }
        return $result;
    }

    /**
     * 隐藏手机号，输入：18910905678，返回：189****5678
     *
     * @param     $phone
     *
     * @return string
     */
    public static function hidePhone($phone)
    {
        $phone  = trim($phone);
        $length = mb_strlen($phone, 'utf-8');
        if($phone && $length)
        {
            $result = mb_substr($phone, 0, 3, 'utf-8') . str_repeat('*', 4) . mb_substr($phone, -4, 4, 'utf-8');

            return $result;
        }
        else
        {
            return "";
        }
    }

    /**
     * 隐藏手机号后4位
     * @param string $phone
     * @return string
     */
    public static function hidePhoneLast($phone)
    {
        $phone  = trim($phone);
        $length = mb_strlen($phone, 'utf-8');
        if($phone && $length)
        {
            $result = mb_substr($phone, 0, 7, 'utf-8') . str_repeat('*', 4);

            return $result;
        }
        else
        {
            return "";
        }
    }

    /**
     * 隐藏身份证号，输入：110101201401013776，返回：110***********3776
     *
     * @param     $iDNumber
     *
     * @return string
     */
    public static function hideIDNumber($iDNumber)
    {
        $iDNumber = trim($iDNumber);
        $length   = mb_strlen($iDNumber, 'utf-8');
        if($length)
        {
            $result = mb_substr($iDNumber, 0, 3, 'utf-8') . str_repeat('*', 11) . mb_substr($iDNumber, -4, 4, 'utf-8');

            return $result;
        }
        else
        {
            return $iDNumber;
        }
    }

    /**
     * 根据身份证号获取年龄。输入：110110198902013322，输出：25
     *
     * @param $idNumber
     *
     * @return bool|int|string
     */
    public static function getAgeFromIdNumber($idNumber)
    {
        $age = 0;
        if(strlen($idNumber) === 18)
        {
            $birthYear = substr($idNumber, 6, 4);
            $year      = date('Y');
            $age       = $year - $birthYear;
        }

        return $age;
    }


    /**
     * 根据身份证号获取性别。输入：110110198902013322，输出：1
     *
     * @param $idNumber
     * 1：男，2：女
     * @return bool|int|string
     */
    public static function getGenderFromIdNumber($idNumber)
    {
        $gender = 0;
        if(strlen($idNumber) === 18)
        {
            $num = substr($idNumber, -2, 1);
            $gender = ($num%2==1) ? 1 : 2;
        }

        return $gender;
    }


    /**
     * 获取字符串长度，默认UTF-8编码
     *
     * @param        $string
     * @param string $encoding
     *
     * @return int
     */
    public static function getStringLength($string, $encoding = 'utf-8')
    {
        return mb_strlen($string, $encoding);
    }

    /**
     * 功能：对字符串中的CSV文件特殊字符进行转义，包括：将回车转为空格、将英文逗号转为中文逗号。
     * @author zhangzhan
     * @param string $strInput
     * @return string
     */
    public static function csvSpecialChars($strInput)
    {
        $strTemp = str_replace(",", "，", $strInput);
        $strTemp = str_replace("\r", " ", $strTemp);
        return str_replace("\n", " ", $strTemp);
    }
	
    /**
     * 功能：判断$str是否以$strPrefix为开头并返回。
     * @author zhangzhan
     * @param string $str
     * @param string $strPrefix
     * @return bool
     */
    public static function startsWith($str, $strPrefix)
    {
        $strPrefixReal = substr($str, 0, strlen($strPrefix));
        return ($strPrefixReal===$strPrefix);
    }
    
    /**
     * 功能：判断$str是否以$strSuffix为结尾并返回。
     * @author zhangzhan
     * @param string $str
     * @param string $strSuffix
     * @return bool
     */
    public static function endsWith($str, $strSuffix)
    {
        $strSuffixReal = substr($str, -strlen($strSuffix));
        return ($strSuffixReal===$strSuffix);
    }

    /**
     * 检测是否为预约单号（aptId）
     * @param $aptId
     * @return bool
     */
    public static function isAptId($aptId)
    {
        if (is_numeric($aptId) && (strlen($aptId) == 13 || strlen($aptId) == 18))
        {
            return true;
        }
        return false;
    }

	/**
	 * 检测是否为中文 只对utf8格式的数据有效
	 * @param $str
	 * @return bool
	 */
	public static function isChinese($str)
	{
	    $chineseReg   = "/[\x{4e00}-\x{9fa5}]+/ius";
		$matchChinese = preg_match($chineseReg, $str);
		if ($matchChinese)
		{
		    return true;
		}

		return false;
	}

    /**
     * @desc  字符串b64解密
     * @param string $str_encoded 密文
     * @return string | null
     **/
    public static function decodeB64($str_encoded) {
        $strDecoded = null;

        if ($str_encoded !== null && $str_encoded !== '') {
            $len = (chpr_B64_Decode_php($str_encoded, 0));
        } else {
            $len = -1;
        }

        if ($len > 0) {
            $strDecoded = substr($str_encoded, 0, $len);
        }

        return $strDecoded;
    }
}
