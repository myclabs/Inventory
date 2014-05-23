<?php

namespace AF\Application\Form\Action;

/**
 * An Action which will hide the element.
 *
 * @author valentin.claras
 */
class Hide extends SetState
{
    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::HIDE;
        $this->_reverseFunctionCalled = self::SHOW;
    }
}
