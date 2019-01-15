<?php

namespace pdate;

/**
 * Class PersianDateTime
 */
class PersianDateTime extends DateTime
{
    /**
     * @var string
     */
    private static $TIME_SEPARATOR = ':';
    /**
     * @var string
     */
    private static $DATE_SEPARATOR = '/';
    /**
     * @var string
     */
    private static $DateTime_SEPARATOR = ' ';
    /**
     * @var string[]
     */
    private static $persianWeekdaysName = array("شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه");
    /**
     * @var string[]
     */
    private static $persianWeekdaysShortName = array("ش", "ی", "د", "س", "چ", "پ", "ج");
    /**
     * @var array
     */
    private static $persianMonthsNames = array("", "فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند");
    /**
     * @var array
     */
    private static $persianMonthsDays = array(0, 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    private static $isTimeZoneSet = false;

    private $timestamp = null;

    /**
     * @param string $time
     * @param DateTimeZone $timezone
     */
    public function __construct($time = 'now', $timezone = NULL)
    {
        $this->timestamp = self::pstrtotime($time);
    }

    /**
     * @param $format
     * @param string $timestamp
     * @return string
     */
    public static function pstrftime($format, $timestamp = "")
    {
        self::setDefaultTimeZone();
        if ($timestamp === "") {
            $timestamp = time();
        }
        $date = date("Y-m-d-w", $timestamp);
        list ($gYear, $gMonth, $gDay, $gWeek) = explode('-', $date);
        list ($pYear, $pMonth, $pDay) = self::gregorianToPersian($gYear, $gMonth, $gDay);
        $pWeek = $gWeek + 1;
        if ($pWeek == 7)
            $pWeek = 0;
        $lenghFormat = strlen($format);
        $i = 0;
        $result = "";
        while ($i < $lenghFormat) {
            $par = $format{$i};
            if ($par == "%") {
                $type = $format{++$i};
                switch ($type) {
                    case 'a' :
                        $result .= substr(self::$persianWeekdaysName [$pWeek], 0, 2);
                        break;
                    case 'A' :
                        $result .= self::$persianWeekdaysName [$pWeek];
                        break;
                    case 'd' :
                        $result .= ($pDay < 10) ? "0" . $pDay : $pDay;
                        break;
                    case 'e' :
                        $result .= $pDay;
                        break;
                    case 'j' :
                        $dayinM = self::DayOfYear($pYear, $pMonth, $pDay);
                        $result .= ($dayinM < 10) ? "00" . $dayinM : (($dayinM < 100) ? "0" . $dayinM : $dayinM);
                        break;
                    case 'u' :
                        $result .= $pWeek + 1;
                        break;
                    case 'w' :
                        $result .= $pWeek;
                        break;
                    case 'U' :
                        $result .= floor(self::DayOfYear($pYear, $pMonth, $pDay) / 7);
                        break;
                    case 'V' :
                    case 'W' :
                        $result .= ceil(self::DayOfYear($pYear, $pMonth, $pDay) / 7);
                        break;
                    case 'b' :
                    case 'h' :
                        $result .= substr(self::$persianMonthsNames [$pMonth], 0, 6);
                        break;
                    case 'B' :
                        $result .= self::$persianMonthsNames [$pMonth];
                        break;
                    case 'm' :
                        $result .= ($pMonth < 10) ? "0" . $pMonth : $pMonth;
                        break;
                    case 'C' :
                        $result .= ceil($pYear / 100);
                        break;
                    case 'g' :
                    case 'y' :
                        $result .= substr($pYear, 2);
                        break;
                    case 'G' :
                    case 'Y' :
                        $result .= $pYear;
                        break;
                    case 'H' :
                    case 'I' :
                    case 'l' :
                    case 'M' :
                    case 'R' :
                    case 'S' :
                    case 'T' :
                    case 'X' :
                    case 'z' :
                    case 'Z' :
                        $result .= strftime("%" . $type, $timestamp);
                        break;
                    case 'p' :
                    case 'P' :
                    case 'r' :
                        if (date('a', $timestamp) == 'am') {
                            $result .= ($type == 'p') ? 'ق.ظ' : (($type == 'P') ? 'قبل از ظهر' : strftime("%I:%M:%S قبل از ظهر", $timestamp));
                        } else {
                            $result .= ($type == 'p') ? 'ب.ظ' : (($type == 'P') ? 'بعد از ظهر' : strftime("%I:%M:%S بعد از ظهر", $timestamp));
                        }
                        break;
                    case 'c' :
                        $result .= substr(self::$persianWeekdaysName [$pWeek], 0, 2) . " " . substr(self::$persianMonthsNames [$pMonth], 0, 6) . " " . $pDay . " " . strftime("%T", $timestamp) . " " . $pYear;
                        break;
                    case 'D' :
                    case 'x' :
                        $result .= (($pMonth < 10) ? "0" . $pMonth : $pMonth) . "/" . (($pDay < 10) ? "0" . $pDay : $pDay) . "/" . substr($pYear, 2);
                        break;
                    case 'F' :
                        $result .= $pYear . "-" . (($pMonth < 10) ? "0" . $pMonth : $pMonth) . "-" . (($pDay < 10) ? "0" . $pDay : $pDay);
                        break;
                    case 's' :
                        $result .= $timestamp;
                        break;
                    case 'n' :
                        $result .= "\n";
                        break;
                    case 't' :
                        $result .= "\t";
                        break;
                    case '%' :
                        $result .= "%";
                        break;
                    default :
                        $result .= "%" . $type;
                }
            } else {
                $result .= $par;
            }
            $i++;
        }
        return $result;
    }

    /**
     *
     */
    public static function setDefaultTimeZone()
    {
        if (!self::$isTimeZoneSet) {
            date_default_timezone_set('Asia/Tehran');
        }
    }

    /**
     * @param $g_y
     * @param $g_m
     * @param $g_d
     * @return array
     */
    public static function gregorianToPersian($g_y, $g_m, $g_d)
    {
        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;
        $g_day_no = 365 * $gy + self::div($gy + 3, 4) - self::div($gy + 99, 100) + self::div($gy + 399, 400);
        for ($i = 0; $i < $gm; ++$i)
            $g_day_no += $g_days_in_month [$i];
        if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
            $g_day_no++;
        $g_day_no += $gd;
        $j_day_no = $g_day_no - 79;
        $j_np = self::div($j_day_no, 12053);
        /* 12053 = 365*33 + 32/4 */
        $j_day_no = $j_day_no % 12053;
        $jy = 979 + 33 * $j_np + 4 * self::div($j_day_no, 1461);
        /* 1461 = 365*4 + 4/4 */
        $j_day_no %= 1461;
        if ($j_day_no >= 366) {
            $jy += self::div($j_day_no - 1, 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }
        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month [$i]; ++$i)
            $j_day_no -= $j_days_in_month [$i];
        $jm = $i + 1;
        $jd = $j_day_no + 1;
        return array($jy, $jm, $jd);
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private static function div($a, $b)
    {
        return ( int )($a / $b);
    }

    /**
     * @param int $pYear
     * @param int $pMonth
     * @param int $pDay
     * @return int
     */
    public static function DayOfYear($pYear, $pMonth, $pDay)
    {
        $days = 0;
        for ($i = 1; $i < $pMonth; $i++) {
            $days += self::$persianMonthsDays [$i];
        }
        return $days + $pDay;
    }

    /**
     * @param string $timestamp
     * @return array
     */
    public static function pgetdate($timestamp = "")
    {
        self::setDefaultTimeZone();
        if ($timestamp === "")
            $timestamp = mktime();
        list ($seconds, $minutes, $hours, $mday, $wday, $mon, $year, $yday, $weekday, $month) = explode("-", self::pdate("s-i-G-j-w-n-Y-z-l-F", $timestamp));
        return array(0 => $timestamp, "seconds" => $seconds, "minutes" => $minutes, "hours" => $hours, "mday" => $mday, "wday" => $wday, "mon" => $mon, "year" => $year, "yday" => $yday, "weekday" => $weekday, "month" => $month);
    }

    /**
     * @param $format
     * @param string $timestamp
     * @return string
     */
    public static function pdate($format, $timestamp = "")
    {
        self::setDefaultTimeZone();
        if ($timestamp === "") {
            $timestamp = time();
        }
        $date = date("Y-m-d-w", $timestamp);
        list ($gYear, $gMonth, $gDay, $gWeek) = explode('-', $date);
        list ($pYear, $pMonth, $pDay) = self::gregorianToPersian($gYear, $gMonth, $gDay);
        $pWeek = $gWeek + 1;
        if ($pWeek == 7)
            $pWeek = 0;
        $lenghFormat = strlen($format);
        $i = 0;
        $result = "";
        while ($i < $lenghFormat) {
            $par = $format{$i};
            if ($par == '\\') {
                $result .= $format{++$i};
                $i++;
                continue;
            }
            switch ($par) {
                case 'd' :
                    $result .= ($pDay < 10) ? "0" . $pDay : $pDay;
                    break;
                case 'D' :
                    $result .= /*substr(*/
                        self::$persianWeekdaysName[$pWeek]/*, 0, 2)*/
                    ;
                    break;
                case 'j' :
                    $result .= $pDay;
                    break;
                case 'l' :
                    $result .= self::$persianWeekdaysName [$pWeek];
                    break;
                case 'N' :
                    $result .= $pWeek + 1;
                    break;
                case 'w' :
                    $result .= $pWeek;
                    break;
                case 'z' :
                    $result .= self::DayOfYear($pYear, $pMonth, $pDay);
                    break;
                case 'W' :
                    $result .= ceil(self::DayOfYear($pYear, $pMonth, $pDay) / 7);
                    break;
                case 'F' :
                    $result .= self::$persianMonthsNames [$pMonth];
                    break;
                case 'm' :
                    $result .= ($pMonth < 10) ? "0" . $pMonth : $pMonth;
                    break;
                case 'M' :
                    $result .= substr(self::$persianMonthsNames [$pMonth], 0, 6);
                    break;
                case 'n' :
                    $result .= $pMonth;
                    break;
                case 't' :
                    $result .= (self::isKabise($pYear) and $pMonth == 12) ? 30 : self::$persianMonthsDays [$pMonth];
                    break;
                case 'L' :
                    $result .= ( int )self::isKabise($pYear);
                    break;
                case 'Y' :
                case 'o' :
                    $result .= $pYear;
                    break;
                case 'y' :
                    $result .= substr($pYear, 2);
                    break;
                case 'a' :
                case 'A' :
                    if (date('a', $timestamp) == 'am') {
                        $result .= ($par == 'a') ? 'am' : 'AM';
                    } else {
                        $result .= ($par == 'a') ? 'pm' : 'PM';
                    }
                    break;
                case 'B' :
                case 'g' :
                case 'G' :
                case 'h' :
                case 'H' :
                case 's' :
                case 'u' :
                case 'i' :
                case 'e' :
                case 'I' :
                case 'O' :
                case 'P' :
                case 'T' :
                case 'Z' :
                    $result .= date($par, $timestamp);
                    break;
                case 'c' :
                    $result .= $pYear . "-" . $pMonth . "-" . $pDay . "T" . date("H::i:sP", $timestamp);
                    break;
                case 'r' :
                    $result .= substr(self::$persianWeekdaysName [$pWeek], 0, 2) . "، " . $pDay . " " . substr(self::$persianMonthsNames [$pMonth], 0, 6) . " " . $pYear . " " . date("H::i:s P", $timestamp);
                    break;
                case 'U' :
                    $result .= $timestamp;
                    break;
                default :
                    $result .= $par;
            }
            $i++;
        }
        return $result;
    }

    /**
     * @param $year
     * @return bool
     */
    public static function isKabise($year)
    {
        $mod = $year % 33;
        if ($mod == 1 or $mod == 5 or $mod == 9 or $mod == 13 or $mod == 17 or $mod == 22 or $mod == 26 or $mod == 30)
            return true;
        return false;
    }

    /**
     * @param $date
     * @param string $separator
     * @return bool|int
     */
    public static function CheckDate($date, $separator = '/')
    {
        list ($y, $m, $d) = explode($separator, $date);
        $date = false;
        if (self::pcheckdate(( int )$m, ( int )$d, ( int )$y)) {
            $date = self::pmktime(0, 0, 0, $m, $d, $y);
        }
        return $date;
    }

    /**
     * @param $month
     * @param $day
     * @param $year
     * @return bool
     */
    public static function pcheckdate($month, $day, $year)
    {
        self::setDefaultTimeZone();
        if ($month < 1 || $month > 12 || $year < 1 || $year > 32767 || $day < 1 || $day > 31) {
            return false;
        }
        if ($day > self::$persianMonthsDays [$month]) {
            if ($month < 12) {
                return false;
            } elseif ($day > 30 && self::isKabise($year)) {
                return false;
            } elseif ($day > 29 && !self::isKabise($year)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $month
     * @param int $day
     * @param int $year
     * @param $is_dst
     * @return int
     */
    public static function pmktime($hour = 0, $minute = 0, $second = 0, $month = 0, $day = 0, $year = 0)
    {
        self::setDefaultTimeZone();
        if ($hour == 0 && $minute == 0 && $second == 0 && $month == 0 && $day == 0 && $year == 0)
            return time();
        list ($year, $month, $day) = self::persianToGregorian($year, $month, $day);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * @param $j_y
     * @param $j_m
     * @param $j_d
     * @return array
     */
    public static function persianToGregorian($j_y, $j_m, $j_d)
    {
        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
        $jy = (int)$j_y - 979;
        $jm = (int)$j_m - 1;
        $jd = (int)$j_d - 1;
        $j_day_no = 365 * $jy + self::div($jy, 33) * 8 + self::div($jy % 33 + 3, 4);
        for ($i = 0; $i < $jm; ++$i)
            $j_day_no += $j_days_in_month [$i];
        $j_day_no += $jd;
        $g_day_no = $j_day_no + 79;
        $gy = 1600 + 400 * self::div($g_day_no, 146097);
        /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
        $g_day_no = $g_day_no % 146097;
        $leap = true;
        if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */ {
            $g_day_no--;
            $gy += 100 * self::div($g_day_no, 36524);
            /* 36524 = 365*100 + 100/4 - 100/100 */
            $g_day_no = $g_day_no % 36524;
            if ($g_day_no >= 365)
                $g_day_no++;
            else
                $leap = false;
        }
        $gy += 4 * self::div($g_day_no, 1461);
        /* 1461 = 365*4 + 4/4 */
        $g_day_no %= 1461;
        if ($g_day_no >= 366) {
            $leap = false;
            $g_day_no--;
            $gy += self::div($g_day_no, 365);
            $g_day_no = $g_day_no % 365;
        }
        for ($i = 0; $g_day_no >= $g_days_in_month [$i] + ($i == 1 && $leap); $i++)
            $g_day_no -= $g_days_in_month [$i] + ($i == 1 && $leap);
        $gm = sprintf("%02d", $i + 1);
        $gd = sprintf("%02d", $g_day_no + 1);
        return array($gy, $gm, $gd);
    }

    /**
     * @return string
     */
    public static function getDateSeparator()
    {
        return self::$DATE_SEPARATOR;
    }

    /**
     * @param $persian
     * @return bool|int
     */
    public static function pstrtotime($persian)
    {
        self::setDefaultTimeZone();
        $dateTime = explode(self::$DateTime_SEPARATOR, $persian);
        $dateParts = explode(self::$DATE_SEPARATOR, $dateTime[0]);
        $year = isset($dateParts[0]) ? +$dateParts[0] : -1;
        $month = isset($dateParts[1]) ? +$dateParts[1] : -1;
        $day = isset($dateParts[2]) ? +$dateParts[2] : -1;
        $isDateValid = self::pcheckdate($month, $day, $year);
        if (isset($dateTime[1])) {
            $timeParts = explode(self::$TIME_SEPARATOR, $dateTime[1]);
            $hour = isset($timeParts[0]) ? +$timeParts[0] : 0;
            $minute = isset($timeParts[1]) ? +$timeParts[1] : 0;
            $second = isset($timeParts[2]) ? +$timeParts[2] : 0;
        } else {
            $hour = 0;
            $minute = 0;
            $second = 0;
        }
        $isTimeValid = self::checkTime($hour, $minute, $second);
        if ($isDateValid && $isTimeValid) {
            return self::pmktime($hour, $minute, $second, $month, $day, $year);
        } else {
            return false;
        }
    }

    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $is12Hour
     * @return bool
     */
    public static function checkTime($hour = 0, $minute = 0, $second = 0, $is12Hour = false)
    {
        if ($hour < 0) {
            return false;
        }
        if ($is12Hour && $hour > 12) {
            return false;
        }
        if (!$is12Hour && $hour > 23) {
            return false;
        }
        if ($minute < 0 && $minute >= 60) {
            return false;
        }
        if ($second < 0 && $second >= 60) {
            return false;
        }
        return true;
    }

    public static function gregorianToPersianString($gString, $time = true)
    {
        if (!$gString) {
            return '';
        }
        $gString = str_replace(array('/', '\\', '.'), '-', $gString);
        $dt = explode(' ', $gString);
        $g = explode('-', $dt[0]);
        $p = self::gregorianToPersian(+$g[0], +$g[1], +$g[2]);
        if($time)
            return implode('/', $p) . (isset($dt[1]) ? ' ' . $dt[1] : '');
        else
            return implode('/', $p);

    }

    public static function PersianToGregorianString($pString)
    {
        $p = explode('/', $pString);
        $g = self::persianToGregorian($p[0], $p[1], $p[2]);
        if($g)
            return implode('-', $g);
        return '';
    }
}