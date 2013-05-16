<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an input type of value input numeric, i.e a value and a percent.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Pattern_Value extends UI_Form_Element_Numeric
{
    /**
     * @var UI_Form_Element_Pattern_Percent
     */
    protected $_percent = null;

    /**
     * Constructor
     *
     * @param string $name
     * @param bool $withPercent
     */
    public function __construct($name, $withPercent=true)
    {
        parent::__construct($name);

        if ($withPercent) {
            // Interface texte pour l'incertitude
            $this->_percent = new UI_Form_Element_Pattern_Percent('percent'.$name);
            $this->getElement()->addElement($this->_percent);
        }

        $this->addValidator('Float');
    }

    /**
     * @param mixed $value
     * @see Zend/Form/Zend_Form_Element::setValue()
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->setValue($value[0]);
            if (isset($this->_percent)) {
                $this->_percent->setValue($value[1]);
            }
        } else {
            // Permett l'affichage des valeurs en fonction de la locale du navigateur
            parent::setValue($value);
        }
    }

    /**
     * Set the value of the percent element.
     * @param unknow_var $value
     */
    public function setPercentValue($value)
    {
        if (isset($this->_percent)) {
            $this->_percent->setValue($value);
        }
    }

    /**
     * Get the percent input associated to this field.
     * @return UI_Form_Element_Pattern_Percent
     */
    public function getPercent()
    {
        return $this->_percent;
    }

}