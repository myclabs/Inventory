<?php

namespace AF\Domain\AF\Input;

use AF\Domain\Algorithm\Input\Input;

/**
 * Input Element for a group.
 *
 * @author matthieu.napoli
 */
class GroupInput extends Input implements Input
{
    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFieldsCompleted()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return false;
    }
}
