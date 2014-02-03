<?php

namespace AF\Domain\Input;

/**
 * Input element for a group.
 *
 * @author matthieu.napoli
 */
class GroupInput extends Input implements \AF\Domain\Algorithm\Input\Input
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
