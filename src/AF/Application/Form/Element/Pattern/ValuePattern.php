<?php

namespace AF\Application\Form\Element\Pattern;

use AF\Application\Form\Element\NumericField;

/**
 * Generate an input type of value input numeric, i.e a value and a percent.
 *
 * @author valentin.claras
 */
class ValuePattern extends NumericField
{
    /**
     * @var PercentPattern
     */
    protected $_percent = null;

    /**
     * @param string $name
     * @param bool   $withPercent
     */
    public function __construct($name, $withPercent = true)
    {
        parent::__construct($name);

        if ($withPercent) {
            // Interface texte pour l'incertitude
            $this->_percent = new PercentPattern('percent' . $name);
            $this->getElement()->addElement($this->_percent);
        }

        $this->addValidator('Float');
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $this->setValue($value[0]);
            if (isset($this->_percent)) {
                $this->_percent->setValue($value[1]);
            }
        } else {
            // Permet l'affichage des valeurs en fonction de la locale du navigateur
            parent::setValue($value);
        }
    }

    /**
     * Set the value of the percent element.
     * @param mixed $value
     */
    public function setPercentValue($value)
    {
        if (isset($this->_percent)) {
            $this->_percent->setValue($value);
        }
    }

    /**
     * Get the percent input associated to this field.
     * @return PercentPattern
     */
    public function getPercent()
    {
        return $this->_percent;
    }
}
