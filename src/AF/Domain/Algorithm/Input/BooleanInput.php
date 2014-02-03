<?php

namespace AF\Domain\Algorithm\Input;

use AF\Domain\Algorithm\Input\Input;

/**
 * @author matthieu.napoli
 */
interface BooleanInput extends Input
{
    /**
     * @return boolean
     */
    public function getValue();
}
