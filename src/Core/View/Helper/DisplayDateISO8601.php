<?php
/**
 * @package    Core
 * @subpackage Helper
 */

/**
 * Aide de vue permettant de formater l'affichage d'une date sous la forme : AAAA-MM-JJ
 *
 * @package    Core
 * @subpackage Helper
 */
class Core_View_Helper_DisplayDateISO8601
{

    /**
     * Formate l'affichage d'une date dans une chaine de caractère
     * @param Core_Date $datetime
     * @return string
     */
    public function displayDateISO8601(Core_Date $datetime)
    {
        if ($datetime != null) {
            $newDate = new Core_Date();
            list($date, $time) = explode(' ', $datetime);
            list($year, $month, $day) = explode('-', $date);
            list($hour, $minute, $second) = explode(':', $time);
            $newDate->setYear("$year");
            $newDate->setMonth($month);
            $newDate->setDay($day);
            $newDate->setHour($hour);
            $newDate->setMinute($minute);
            $newDate->setSecond($second);
            return $newDate->formatDate('yy-MM-dd');
        } else {
            return null;
        }
    }

}
