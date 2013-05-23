<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input text.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_Date extends UI_Form_Element_Text
{
    /**
     * Constructor
     *
     * @param string $name
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->addValidator('Date');

        $this->getElement()->addSuffix($this->getSuffixDatepickerComponent());
    }

    /**
     * Get The HTML Suffix Component wich will bring on the datepicker.
     *
     * @return string
     */
    public function getSuffixDatePickerComponent()
    {
        $component = '';

        $component .= '<i class="icon-th date"></i>';

        return $component;
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        $days = Zend_Locale_Data::getList(Core_Locale::loadDefault()->getId(), 'days');
        $weekInfos = Zend_Locale_Data::getList(Core_Locale::loadDefault()->getId(), 'week');
        $weekStart = ((int) $days['format']['narrow'][$weekInfos['firstDay']]) - 1;

        //@todo Date : Trouver un moyen de récupérer le format en fonction de la locale.
        $dateFormat = 'dd/mm/yyyy';

        $script .= '$(\'#'.$this->getId().'\').parent().datepicker({';
        $script .= 'format: \''.$dateFormat.'\'';
        $script .= ', ';
        $script .= 'weekStart: '.$weekStart;
        $script .= '});';

        return $script;
    }

}