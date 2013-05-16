<?php
/**
 * @package    Core
 * @subpackage Date
 */

/**
 * Classe de date
 *
 * @package    Core
 * @subpackage Date
 *
 * @uses Zend_Date
 */
class Core_Date extends Zend_Date
{

    /**
     * @param  string|integer|Zend_Date|array  $date    OPTIONAL Date value or value of date part to set
     *                                                 ,depending on $part. If null the actual time is set.
     * @param  string                          $part    OPTIONAL Defines the input format of $date.
     * @param  string|Zend_Locale              $locale  OPTIONAL Locale for parsing input.
     *
     * @throws Zend_Date_Exception
     *
     * @return Core_Date
     */
    public function __construct($date = null, $part = null, $locale = null)
    {
        date_default_timezone_set('Europe/Paris');
        if ($locale == null) {
            $locale = 'fr_FR';
        }
        parent::__construct($date, $part, $locale);
    }

    /**
     * Return the time with the format like $format
     * @param string $format
     * @return string
     */
    public function formatDate($format)
    {
        return $this->toString($format);
    }

    /**
     * Returns the actual date as new date object
     *
     * @param  string|Zend_Locale $locale  OPTIONAL Locale for parsing input
     * @return Core_Date
     */
    public static function now($locale = null)
    {
        return new Core_Date(time(), self::TIMESTAMP, $locale);
    }

}