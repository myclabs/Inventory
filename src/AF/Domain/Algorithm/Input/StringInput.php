<?php

namespace AF\Domain\Algorithm\Input;

/**
 * @author  matthieu.napoli
 */
interface StringInput extends Input
{
    /**
     * @return string
     */
    public function getValue();
}
