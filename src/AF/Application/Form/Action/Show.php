<?php

namespace AF\Application\Form\Action;

/**
 * An Action which will show the element.
 *
 * @author valentin.claras
 */
class Show extends SetState
{
    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::SHOW;
        $this->_reverseFunctionCalled = self::HIDE;
    }
}
