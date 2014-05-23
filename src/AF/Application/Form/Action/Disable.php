<?php

namespace AF\Application\Form\Action;

/**
 * An Action which will disable an element.
 *
 * @author valentin.claras
 */
class Disable extends SetState
{
    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::DISABLE;
        $this->_reverseFunctionCalled = self::ENABLE;
    }
}
