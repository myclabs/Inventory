<?php

namespace AccountingForm\Domain\Processing;

use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;

interface ProcessingStep
{
    /**
     * @param ValueSet $input
     * @throws ProcessingException
     * @return ValueSet Output
     */
    public function execute(ValueSet $input);

    /**
     * @return AlgoConfigurationError
     */
    public function validate();
}
