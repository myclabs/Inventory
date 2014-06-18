<?php

namespace AF\Application\Form\Element\Pattern;

use AF\Application\Form\Element\NumericField;
use Core_Exception_InvalidArgument;

/**
 * Generate an input type of percent input text.
 *
 * @author valentin.claras
 */
class PercentPattern extends NumericField
{
    /**
     * @param string $name
     * @param bool   $isTypeNumber
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $isTypeNumber = false)
    {
        parent::__construct($name, $isTypeNumber);

        $this->setAttrib('class', 'incertitude form-control');
        $this->setAttrib('size', 3);

        if ($isTypeNumber) {
            $this->setAttrib('min', 0);
            $this->setAttrib('max', 100);
            $this->setAttrib('step', 1);
        } else {
            $this->setAttrib('pattern', '(100)|[0-9]{1,2}');
        }
        $this->addValidator('Float');
        $this->addValidator('Between', false, array(0, 100));

        $this->getElement()->addPrefix('±');
        $this->getElement()->addSuffix('%');
    }
}
