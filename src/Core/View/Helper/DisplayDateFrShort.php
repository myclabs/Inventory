<?php
/**
 * @package    Core
 * @subpackage Helper
 */

/**
 * Aide de vue permettant de formater l'affichage l'affichage d'une date sous la forme : JJ-MM-AAAA
 *
 * @package    Core
 * @subpackage Helper
 */
class Core_View_Helper_DisplayDateFrShort
{

    /**
     * Formate l'affichage d'une date dans une chaine de caractère
     * @param DATETIME $datetime
     * @return string
     */
    public function displayDateFrShort($datetime)
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
            return $newDate->formatDate('dd/MM/yyyy');
        } else {
            return null;
        }
    }

}
