<?php

/**
 * @name Library_DateTime
 * @desc 日期格式化类，各种日期格式转换
 * @date   2016-10-11
 */
Class Library_DateTime
{

    //不显示第二天预约的时间点
    const TIME_NO_NEXT_DAY_APPOINTMENT = "16:00:00";

    /**
     * 获取当前时间日期，如：2014-06-19 14:15:30
     * @param string $format
     * @return bool|string
     */
    public static function getNowDateTime($format = "Y-m-d H:i:s")
    {
        return date($format);
    }


    /**
     * 获取距离起始日（默认当前日期)$days天后的日期,假设今天是2014-07-04，那8天后的日期则是2014-07-12
     *
     * @param        $days ，正数表示之后，负数表示之前
     * @param string $format
     *
     * @return bool|string
     */
    public static function getAfterSomeDayDate($days, $format = 'Y-m-d', $nowDate = '')
    {
        if (empty($nowDate))
        {
            $nowDate = date($format);
        }

        if (!is_int($days))
        {
            return $nowDate;
        }
        $thatDate = date($format, strtotime($nowDate) + 60 * 60 * 24 * $days);

        return $thatDate;
    }
    
    /**
     * 获取距离起始日（默认当前日期)$days天后的第一个 1号
     *
     * @param        $days ，正数表示之后，负数表示之前
     * @param string $format
     *
     * @return bool|string
     */
    public static function getAfterSomeDayNo1($days, $format = 'Y-m-d', $nowDate = '')
    {
        if (empty($nowDate))
        {
            $nowDate = date($format);
        }

        if (!is_int($days))
        {
            return $nowDate;
        }
        
        $thatTime = strtotime($nowDate) + 60 * 60 * 24 * $days;
        $thatMonth = date('Y-m-01', $thatTime);
        $thatDate = date($format, strtotime("$thatMonth + 1 month"));

        return $thatDate;
    }

    /**
     * @param string  $dateOrigin 源点日期（含时分秒）
     * @param string $dateDestination 目标点日期 目标点应比源点大
     * @param string $unit 单位 s秒 h小时 d天,默认以天为单位
     * @param int    $length 保留小数点位数，默认为0
     * @return float
     */
    public static function getDatesDistance($dateOrigin, $dateDestination, $unit = 'd', $length = 3)
    {
        switch ($unit)
        {
            case "s":
                $divisor = 1;
                break;
            case "h":
                $divisor = 3600;
                break;
            default:
                $divisor = 3600 * 24;
        }
        return round(abs(strtotime($dateDestination) - strtotime($dateOrigin)) / ($divisor), $length);
    }

    /**
     * 根据时间戳或者日期返回星期
     * @param    $date
     * @return    string
     */
    public static function getWeekdayByTime($date)
    {

        if (!(is_numeric($date)) || !(strlen($date) == 10))
        {
            $date = strtotime($date);
        }
        $weekday    = array('周日', '周一', '周二', '周三', '周四', '周五', '周六');
        $weekdayNum = date('w', $date);
        return $weekday[$weekdayNum];
    }

    /**
     * 根据时间戳或者日期返回星期int
     * @param    $date
     * @return    string
     */
    public static function getWeekdayIntByTime($date)
    {

        if (!(is_numeric($date)) || !(strlen($date) == 10))
        {
            $date = strtotime($date);
        }

        $weekday    = array(7, 1, 2, 3, 4, 5, 6);
        $weekdayNum = date('w', $date);

        return $weekday[$weekdayNum];
    }

    /**
     * @param string $date 日期
     * @param array $weekday 中文列表
     *
     * @return mixed 根据时间戳或者日期返回星期（字面例如六）
     *
     */
    public static function  getWeekdayWordByTime($date, $weekday = array())
    {
        if (!(is_numeric($date)) || !(strlen($date) == 10))
        {
            $date = strtotime($date);
        }

        if(empty($weekday) || count($weekday) != 7)
        {
            $weekday    = array("日", "一", "二", "三", "四", "五", "六");
        }
        $weekdayNum = date('w', $date);

        return $weekday[$weekdayNum];
    }
    /**
     * 日期转毫秒
     *
     * @param $date
     *
     * @return int
     */
    public static function dateToTime($date)
    {
        return strtotime($date);
    }

    /**
     * 返回起始和终止日期之间的日期列表
     * @param $startDate
     * @param $endDate
     * @param string $format
     * @param int $maxLenth
     * @return array
     */
    public static function getDateListByStartAndEnd($startDate, $endDate, $format = 'Y-m-d', $maxLenth = 100)
    {
        $timestampStart = strtotime($startDate);
        $timestampEnd   = strtotime($endDate);
        //判断是否终止时间小于起始时间
        if ($timestampEnd < $timestampStart)
        {
            return array();
        }

        $tmpDate = $startDate;
        $retList = array($tmpDate);
        $count   = 1;
        while ($tmpDate != $endDate)
        {
            $tmpDate   = self::getAfterSomeDayDate(1, $format, $tmpDate);
            $retList[] = $tmpDate;
            $count++;
            if ($count >= $maxLenth)
            {
                break;
            }
        }
        return $retList;
    }

    //TODO 这个函数业务相关，不应该放在这里
    /**
     * 是否是在X天内评价
     * @param     $treatTime    2014-10-21
     * @param     $evaluateTime 2014-10-23
     * @param int $xDays
     *
     * @return bool
     */
    public static function isInXDaysEvaluate($treatTime, $evaluateTime, $xDays = 3)
    {
        return strtotime($treatTime) >= strtotime($evaluateTime) - $xDays * 60 * 60 * 24;
    }

    /**
     * @param string $date   日期
     * @param string $format 日期格式
     *
     * @return bool
     * 判断日期是否失效，超过时间，日期失效
     */
    public static function checkNextDayIsValid($date, $format = "Y-m-d")
    {
        $isValid     = true;
        $currentTime = date("H:i:s");
        //下午四点之后不显示第二天的
        if ($date == self::getAfterSomeDayDate(1, $format)
            && $currentTime > self::TIME_NO_NEXT_DAY_APPOINTMENT
        )
        {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * 根据日期得到该日期属于未来第几周
     *
     * @param $date
     * @return bool|int
     */
    public static function getWeekIndexByDate($date)
    {
        $currDate = date("Y-m-d");
        if (strtotime($date) < strtotime($currDate))
        {
            return false;
        }
        $dateRange = self::getDatesDistance($currDate, $date);
        $weekNum   = ceil($dateRange / 7);

        for ($i = 1; $i <= $weekNum; $i++)
        {
            $strTimeParam = "next monday +$i week";
            $nextWeekTime = strtotime($strTimeParam);
            if (strtotime($date) < $nextWeekTime)
            {
                return $i - 1;
            }
        }
        return false;
    }

    /**
     * 根据日期返回是第几周
     * @param     $date 2014-10-21
     * @return int $num
     */
    public static function getTabIndexByDate($date, $arrDate)
    {

        if (empty($date))
        {
            $date = $arrDate[0];
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date))
        {
            $date = self::getTabIndexByString($date, $arrDate);
        }

        $tsOne  = strtotime('next monday');
        $tsTwo  = strtotime('next monday +1 week');
        $tsThree  = strtotime('next monday +2 week');
        $tsFour  = strtotime('next monday +3 week');
        $tsFive  = strtotime('next monday +4 week');
        $tsDate = strtotime($date);

        $num = 0;
        if ($tsDate < $tsOne)
        {
            $num = 0;
        }
        elseif ($tsDate >= $tsOne && $tsDate < $tsTwo)
        {
            $num = 1;
        }
        elseif($tsDate >= $tsTwo && $tsDate < $tsThree){
            $num = 2;
        }
        elseif($tsDate >= $tsThree && $tsDate < $tsFour) {
            $num = 3;
        }
        elseif ($tsDate >= $tsFour && $tsDate < $tsFive) {
            $num = 4;
        }

        return $num;
    }

    /**
     * 根据字符串返回可预约日期
     * @param string $date workday weekend 空串
     * @return int $num
     */
    public static function getTabIndexByString($string, $arrDate)
    {

        if ($string == 'workday')
        {
            foreach ($arrDate as $date)
            {
                if (date('N', strtotime($date)) < 6)
                {
                    return $date;
                }
            }
        }
        elseif ($string == 'weekend')
        {
            foreach ($arrDate as $date)
            {
                if (date('N', strtotime($date)) > 5)
                {
                    return $date;
                }
            }
        }
        else
        {
            return $arrDate[0];
        }
    }

    /**
     * @param int $dayOfTheWeek 周几, 1-7
     * @param int $extendedDays 考虑接下来多少天
     *
     * @return array
     * 返回接下来的$extendedDays内为周$dayOfTheWeek的日期，默认格式为2014-08-12
     */
    public static function mapWeek2DateList($dayOfTheWeek, $extendedDays, $format = "Y-m-d")
    {
        $dateList      = array();
        $weekValueList = array();
        if (is_numeric($dayOfTheWeek) && $dayOfTheWeek > 0)
        {
            $weekValueList = array($dayOfTheWeek);
        }
        else if ($dayOfTheWeek == -1 || $dayOfTheWeek == "workday")
        {
            $weekValueList = array(1, 2, 3, 4, 5);
        }
        else if ($dayOfTheWeek == -2 || $dayOfTheWeek == "weekend")
        {
            $weekValueList = array(6, 7);
        }
        for ($iter = 1; $iter <= $extendedDays; $iter++)
        {
            $curDate = self::getAfterSomeDayDate($iter, $format);
            if (!self::checkNextDayIsValid($curDate))
            {
                continue;
            }
            if (in_array(date("N", strtotime($curDate)), $weekValueList))
            {
                $dateList[] = $curDate;
            }
        }

        return $dateList;
    }

    /**
     * @param string $startDate    起始日
     * @param int    $extendedDays 距离起始日的天数
     * @param string $format       起始日的日期格式
     *
     * @return array 返回起始日后的extendedDays内按日期列表
     *
     */
    public static function getExtendedDateList($startDate, $extendedDays, $format = "Y-m-d")
    {
        $dateList = array();
        for ($iter = 1; $iter <= $extendedDays; $iter++)
        {
            $curDate = self::getAfterSomeDayDate($iter, $format, $startDate);
            if (!self::checkNextDayIsValid($curDate))
            {
                continue;
            }
            $dateList[] = $curDate;
        }

        return $dateList;
    }

    /**
     * @param string $startDate    起始日
     * @param int    $extendedDays 距离起始日的天数
     * @param string $format       起始日的日期格式
     * @param bool $openToday 是否开放当天号
     * @param bool $openToday 是否开放当天号
     *
     * @return array 返回起始日后的extendedDays内按照周分割列表
     *
     */
    public static function getDateListSortByWeek($startDate, $extendedDays, $format = "Y-m-d", $openToday = false)
    {
        $dateList = array();
        //本周下标为0  下一周 下标为1 下两周 下标为2 依此类推
        $dayOfTheWeek = date('N', strtotime($startDate));
        if($openToday)
        {
            $iter = 0;
        }
        else
        {
            $iter = 1;
        }
        for (; $iter <= $extendedDays; $iter++)
        {
            $weekIndex = floor(($iter + $dayOfTheWeek) / 7);
            //如果是周日或者距离为7天，则算上一周，所以需要减一
            if (($iter + $dayOfTheWeek) % 7 == 0)
            {
                $weekIndex -= 1;
            }
            $dateList[$weekIndex][] = self::getAfterSomeDayDate($iter, $format, $startDate);
        }

        return $dateList;
    }

    /**
     * 判断是否在活动规定的时间内评价
     *
     * @param $date
     * @param $days
     *
     * @return bool
     */
    public static function isTheTimeForEvaluate($date, $days = 3)
    {
        $threeDaysAgo = self::getAfterSomeDayDate(-$days);
        if (strtotime($threeDaysAgo) <= strtotime($date))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 根据时间戳或者日期返回月和天
     * @param    $date
     * @return    string 'n-j'
     */
    public static function getMbDateByTime($date)
    {
        if (!(is_numeric($date)) || !(strlen($date) == 10))
        {
            $date = strtotime($date);
        }

        $Map = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十', '二十一', '二十二', '二十三', '二十四', '二十五', '二十六', '二十七', '二十八', '二十九', '三十', '三十一');

        $monthNum = intval(date('n', $date));
        $dayNum   = intval(date('j', $date));

        return $Map[$monthNum] . '月' . $Map[$dayNum] . '日';
    }

    /**
     * 根据时间戳或者日期返回月和天
     * @param    $date
     * @return    string 'm-d'
     */
    public static function getDateByTime($date)
    {
        if (!(is_numeric($date)) || !(strlen($date) == 10))
        {
            $date = strtotime($date);
        }

        $strDate = date('n月j日', $date);
        return $strDate;
    }

    /**
     * @param        $hours
     * @param string $format
     *
     * @return array
     */
    public static function getTimeBeforeHour($hours, $format = 'Y-m-d H')
    {
        $now = date($format);
        list($nowDate, $nowHour) = explode(" ", $now);

        if (!is_int($hours))
        {
            return array(
                'date' => $nowDate,
                'hour' => $nowHour,
            );
        }
        $thatDate = date($format, time() - 60 * 60 * $hours);
        list($date, $hour) = explode(" ", $thatDate);
        return array(
            'date' => $date,
            'hour' => $hour,
        );
    }

    /**
     * @param        $minutes
     * @param string $datetime
     *
     * @return bool|string
     */
    public static function getTimeAfterXMinute($minutes, $datetime = '')
    {
        if(empty($datetime))
        {
            $datetime = self::getNowDateTime();
        }
        $afterTime = strtotime($datetime) + $minutes * 60;
        return date('Y-m-d H:i:s', $afterTime);
    }

    /**
     * @param        $seconds
     * @param string $datetime
     *
     * @return bool|string
     */
    public static function getTimeAfterXSeconds($seconds, $datetime = '')
    {
        if(empty($datetime))
        {
            $datetime = self::getNowDateTime();
        }
        $afterTime = strtotime($datetime) + $seconds;
        return date('Y-m-d H:i:s', $afterTime);
    }

    /**
     * 获取接下来N天的日期列表
     *
     * @param $n 天数
     * @param $format 格式
     * @param $startAtToday 是否从今天开始
     * @return array 日期列表
     */
    public static function getNextNDayList($n, $format="Y-m-d", $startAtToday=true)
    {
        if ($n < 1) {
            return array();
        }
        $dateList = array();
        $start = time();
        if (!$startAtToday)
        {
            $start += 86400;
        }
        $end = $start + $n * 86400;
        for ($i=$start; $i<$end; $i+=86400)
        {
            $dateList[] = date($format, $i);
        }
        return $dateList;
    }

    /**
     * 获取接下来N天的工作日列表
     *
     * @param $n 天数
     * @param $format 格式
     * @param $startAtToday 是否从今天开始
     * @return array 日期列表
     */
    public static function getWorkdayListFromNextNDay($n, $format="Y-m-d", $startAtToday=true)
    {
        $dateList = self::getNextNDayList($n, $format, $startAtToday);
        $workday = array(1, 2, 3, 4, 5);
        foreach ($dateList as $k => $d)
        {
            $day = date("N", strtotime($d));
            if (!in_array($day, $workday)) {
                unset($dateList[$k]);
            }
        }
        return array_values($dateList);
    }

    /**
     * 获取接下来N天的周末列表
     *
     * @param $n 天数
     * @param $format 格式
     * @param $startAtToday 是否从今天开始
     * @return array 日期列表
     */
    public static function getWeekendListFromNextNDay($n, $format="Y-m-d", $startAtToday=true)
    {
        $dateList = self::getNextNDayList($n, $format, $startAtToday);
        $workday = array(6, 7);
        foreach ($dateList as $k => $d)
        {
            $day = date("N", strtotime($d));
            if (!in_array($day, $workday)) {
                unset($dateList[$k]);
            }
        }
        return array_values($dateList);
    }

    /**
     * 返回最近n天（包括今天）的时间范围和展示文本。
     * @author zhangzhan
     * @param int $intNumberDay 天数
     * @return array 例如：array("2015-06-11 00:00:00,2015-06-12 00:00:00"=>"2015-06-11（周四）")
     */
    public static function getLatestDays($intNumberDay)
    {
        //计算从前$intNumberDay-1天到明天的日期
        $arrDates = array();
        for ($i = -($intNumberDay-1); $i <= 1; $i++)
        {
        $arrDates[] = date("Y-m-d", strtotime("{$i} day"));
        }
    
        $arrLatestDays = array();
        for ($i = $intNumberDay-1; $i >= 0; $i--)
        {
        $strWeekday = self::getWeekdayByTime($arrDates[$i]);
        $arrLatestDays["{$arrDates[$i]} 00:00:00,{$arrDates[$i+1]} 00:00:00"] = "{$arrDates[$i]}（{$strWeekday}）";
        }
    
        return $arrLatestDays;
    }

    /**
     * 格式化时间
     * @param        $timestamp
     * @param string $format
     * @return bool|string
     */
    public static function formatDate($timestamp, $format = "Y-m-d H:i:s")
    {
        return date($format, $timestamp);
    }

    /**
     * @desc 获取人们对日期的习惯性称呼，例如今天，明天，后天/本周x/下周x
     * @param string $date 指定日期
     * @param string $baseDate 基准日期，通常是当天
     * @return string 对日期的习惯性称呼，今天，明天....
     */
    public static function getHabitualDay($date, $baseDate = '')
    {
        if(empty($baseDate))
        {
            $baseDate = date('Y-m-d');
        }
        $weekNow   = date("W");
        $week    = date("W", strtotime($date));
        $dayDistance = Library_DateTime::getDatesDistance($baseDate, $date);
        switch($dayDistance)
        {
            case 0:
                $day = "今天";
                break;
            case 1:
                $day = "明天";
                break;
            case 2:
                $day = "后天";
                break;
            default:
                if ($week == $weekNow)
                {
                    $day = "本".Library_DateTime::getWeekdayByTime($date);
                }
                elseif (1 == $week - $weekNow)
                {
                    $day = "下".Library_DateTime::getWeekdayByTime($date);
                }
                else
                {
                    $day = Library_DateTime::getWeekdayByTime($date);
                }
                break;
        }

        return $day;
    }
}




