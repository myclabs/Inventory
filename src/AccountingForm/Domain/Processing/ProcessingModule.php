<?php

namespace AccountingForm\Domain\Processing;

use AccountingForm\Domain\ValueSet;

interface ProcessingModule
{
    /**
     * @param ValueSet $input
     * @return ValueSet Output
     */
    public function execute(ValueSet $input);
}
