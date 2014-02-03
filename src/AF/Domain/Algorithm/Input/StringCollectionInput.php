<?php

namespace AF\Domain\Algorithm\Input;

/**
 * @author  matthieu.napoli
 */
interface StringCollectionInput extends Input
{
    /**
     * @return string[]
     */
    public function getValue();
}
