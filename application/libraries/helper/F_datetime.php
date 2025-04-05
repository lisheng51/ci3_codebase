<?php

/**
 * F_datetime
 */
class F_datetime
{

    /**
     * 
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validateDate(string $date = "", string $format = 'Y-m-d'): bool
    {
        $min = date($format, strtotime("-99 year"));
        $max = date($format, strtotime("+10 year"));
        if ($date < $min || $date > $max) {
            return false;
        }
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * 
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    public static function random(string $start_date = "", string $end_date = ""): int
    {
        $min = strtotime($start_date);
        $max = strtotime($end_date);
        $val = mt_rand($min, $max);
        return $val;
    }

    /**
     * 
     * @param mix $val
     * @param string $format
     * @return string
     */
    public static function convert_datetime($val = "", string $format = 'd-m-Y H:i:s'): string
    {
        if (empty($val)) {
            return "";
        }
        $obj = date_create($val);
        if ($obj === false) {
            return "";
        }
        return date_format($obj, $format);
    }

    /**
     * 
     * @param mix $val
     * @param string $format
     * @return string
     */
    public static function convert_date($val = "", string $format = 'd-m-Y'): string
    {
        if (empty($val)) {
            return "";
        }
        $obj = date_create($val);
        if ($obj === false) {
            return "";
        }
        return date_format($obj, $format);
    }

    /**
     * 
     * @param int $id
     * @param int $year_back
     * @param int $max_year
     * @param bool $with_empty
     * @param bool $is_required
     * @return string
     */
    public static function select_year(int $id = 0, int $year_back = 17, int $max_year = 60, bool $with_empty = false, bool $is_required = true): string
    {
        $required = $is_required === true ? "required" : null;
        $end = date("Y") - $year_back;
        $start = $end - $max_year;
        $select = '<select name="year" ' . $required . ' class="form-control">';
        $select .= $with_empty === true ? "<option value='' >Jaar</option>" : "";
        for ($year = $start; $year <= $end; $year++) {
            $ckk = $id == $year ? "selected" : '';
            $select .= "<option value=$year $ckk >$year</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * 
     * @param int $id
     * @param int $year
     * @param bool $with_empty
     * @param bool $is_required
     * @return string
     */
    public static function select_week(int $id = 0, int $year = 0, bool $with_empty = true, bool $is_required = true): string
    {
        $required = $is_required === true ? "required" : null;
        $resultYear = $year <= 0 ? date("Y") : $year;
        $date = new DateTime;
        $date->setISODate($resultYear, 53);
        $weekMax = ($date->format("W") === "53" ? 53 : 52);
        $select = '<select name="week" ' . $required . '  class="form-control">';
        $select .= $with_empty === true ? "<option value='' >Week</option>" : "";
        for ($i = 1; $i <= $weekMax; $i++) {
            $week = mb_strlen($i) === 1 ? '0' . $i : $i;
            $ckk = $id == $week ? "selected" : '';
            $select .= "<option value=$week $ckk >$week</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * 
     * @param int $id
     * @param bool $with_empty
     * @param bool $is_required
     * @return string
     */
    public static function select_day(int $id = 0, bool $with_empty = false, bool $is_required = true): string
    {
        $required = $is_required === true ? "required" : null;
        $select = '<select name="day" ' . $required . ' class="form-control">';
        $select .= $with_empty === true ? "<option value='' >Dag</option>" : "";
        for ($day = 1; $day <= 31; $day++) {
            $ckk = $id == $day ? "selected" : '';
            $select .= "<option value=$day $ckk >$day</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * 
     * @param int $total_min
     * @return string
     */
    public static function min_conver_hour(int $total_min = 0): string
    {
        if ($total_min <= 0) {
            return "0(u): 0(m)";
        }
        $hour = floor($total_min / 60);
        $minute = $total_min % 60; //$total_min - $hour * 60;  
        return $hour . " (u):" . $minute . " (m)";
    }

    /**
     * 
     * @param string $date
     * @return int
     */
    public static function cal_age(string $date = ""): int
    {
        $bday = new DateTime($date);  //Y-m-d
        if ($bday === null) {
            return 0;
        }
        $date_now = date("Y-m-d");
        $today = new DateTime($date_now);
        $diff = $today->diff($bday);
        return $diff->y;
    }

    /**
     * 
     * @param int $id
     * @param bool $with_empty
     * @param bool $is_required
     * @return string
     */
    public static function select_month(int $id = 0, bool $with_empty = false, bool $is_required = true): string
    {
        $required = $is_required === true ? "required" : null;
        $select = '<select name="month" ' . $required . ' class="form-control">';
        $select .= $with_empty === true ? "<option value='' >Maand</option>" : "";
        for ($month = 1; $month <= 12; $month++) {
            $ckk = $id == $month ? "selected" : '';
            $month_name = self::month_name($month);
            $select .= "<option value=$month $ckk >$month_name</option>";
        }
        $select .= '</select>';
        return $select;
    }

    /**
     * 
     * @param string $name
     * @param bool $short
     * @return string
     */
    public static function day_name(string $name = 'Mon', bool $short = true): string
    {
        switch ($name) {
            case 'Mon':
                return $short === true ? "Ma" : "Maandag";
            case 'Tue':
                return $short === true ? "Di" : "Dinsdag";
            case 'Wed':
                return $short === true ? "Wo" : "Woensdag";
            case 'Thu':
                return $short === true ? "Do" : "Donderdag";
            case 'Fri':
                return $short === true ? "Vr" : "Vrijdag";
            case 'Sat':
                return $short === true ? "Za" : "Zaterdag";
            case 'Sun':
                return $short === true ? "Zo" : "Zondag";
            default:
                return "";
        }
    }

    /**
     * 
     * @param int $n
     * @return string
     */
    public static function quarter_name(int $n = 1): string
    {
        switch ($n) {
            case 1:
                return "Januari t/m Maart";
            case 2:
                return "April t/m Juni";
            case 3:
                return "Juli t/m September";
            case 4:
                return "Oktober t/m December";
            default:
                return "";
        }
    }

    /**
     * 
     * @param int $n
     * @param bool $short
     * @return string
     */
    public static function month_name(int $n = 1, bool $short = false): string
    {
        switch ($n) {
            case 1:
                return $short === true ? "JAN" : "Januari";
            case 2:
                return $short === true ? "FEB" : "Februari";
            case 3:
                return $short === true ? "MAA" : "Maart";
            case 4:
                return $short === true ? "APR" : "April";
            case 5:
                return $short === true ? "MEI" : "Mei";
            case 6:
                return $short === true ? "JUN" : "Juni";
            case 7:
                return $short === true ? "JUL" : "Juli";
            case 8:
                return $short === true ? "AUG" : "Augustus";
            case 9:
                return $short === true ? "SEP" : "September";
            case 10:
                return $short === true ? "OKT" : "Oktober";
            case 11:
                return $short === true ? "NOV" : "November";
            case 12:
                return $short === true ? "DEC" : "December";
            default:
                return "";
        }
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public static function monthNameToNumber(string $name = ''): string
    {
        $monthName = strtolower($name);
        switch ($monthName) {
            case 'januari':
                return "01";
            case 'februari':
                return "02";
            case 'maart':
                return "03";
            case 'april':
                return "04";
            case 'mei':
                return "05";
            case 'juni':
                return "06";
            case 'juli':
                return "07";
            case 'augustus':
                return "08";
            case 'september':
                return "09";
            case 'oktober':
                return "10";
            case 'november':
                return "11";
            case 'december':
                return "12";
            default:
                return "";
        }
    }

    /**
     * 
     * @param int $inputyear
     * @return array
     */
    public static function free_days(int $inputyear = 0): array
    {
        $year = date("Y");
        if ($inputyear > 0) {
            $year = $inputyear;
        }
        $days = [];
        $nieuwjaar = new DateTime($year . "-01-01");
        $pasen = new DateTime();
        $pasen->setTimestamp(easter_date($year));

        $i_vrijdag = new DateInterval('P2D');
        $i_vrijdag->invert = 1;

        $goedeVrijdag = clone $pasen;
        $goedeVrijdag->add($i_vrijdag); // 2 dagen voor Pasen.

        $paasMaandag = clone $pasen;
        $paasMaandag->add(new DateInterVal('P1D')); // 1 dag na pasen

        $koningsdag = new DateTime($year . "-04-27");
        $bevrijdingsdag = new DateTime($year . "-05-05");

        $hemelvaart = clone $pasen;
        $hemelvaart->add(new DateInterVal('P39D')); // 39 dagen na pasen

        $pinksteren = clone $hemelvaart;
        $pinksteren->add(new DateInterVal('P10D')); // 10 dagen na OLH hemelvaart
        $pinksterMaandag = clone $pinksteren;
        $pinksterMaandag->add(new DateInterVal('P1D')); // 1 dag na pinksteren

        $kerst1 = new DateTime($year . "-12-25");
        $kerst2 = new DateTime($year . "-12-26");

        $days[] = ["day_string" => $nieuwjaar->format('Y-m-d'), "day_name" => "Nieuwjaarsdag"];
        $days[] = ["day_string" => $goedeVrijdag->format('Y-m-d'), "day_name" => "Goede vrijdag"];
        $days[] = ["day_string" => $pasen->format('Y-m-d'), "day_name" => "Eerste paasdag"];
        $days[] = ["day_string" => $paasMaandag->format('Y-m-d'), "day_name" => "Tweede paasdag"];
        $days[] = ["day_string" => $koningsdag->format('Y-m-d'), "day_name" => "Koningsdag"];
        $days[] = ["day_string" => $bevrijdingsdag->format('Y-m-d'), "day_name" => "Bevrijdingsdag"];
        $days[] = ["day_string" => $hemelvaart->format('Y-m-d'), "day_name" => "Hemelvaartsdag"];
        $days[] = ["day_string" => $pinksteren->format('Y-m-d'), "day_name" => "Eerste pinksterdag"];
        $days[] = ["day_string" => $pinksterMaandag->format('Y-m-d'), "day_name" => "Tweede pinksterdag"];
        $days[] = ["day_string" => $kerst1->format('Y-m-d'), "day_name" => "Eerste kerstdag"];
        $days[] = ["day_string" => $kerst2->format('Y-m-d'), "day_name" => "Tweede kerstdag"];

        return $days;
    }
}
