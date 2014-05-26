<?php

namespace AF\Application\Form\Action;

/**
 * An Action which will enable the element.
 *
 * @author valentin.claras
 */
class Enable extends SetState
{
    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::ENABLE;
        $this->_reverseFunctionCalled = self::DISABLE;
    }
}
