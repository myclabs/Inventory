<?php
/**
 * @author matthieu.napoli
 */

use User\Event\UserEvent;
use User\Event\UserEventTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event: an input was edited
 */
class AF_Service_InputEditedEvent extends Event implements UserEvent
{
    use UserEventTrait;

    const NAME = 'af.inputEdited';

    /**
     * @var AF_Model_InputSet_Primary
     */
    private $inputSet;

    /**
     * @param AF_Model_InputSet_Primary $inputSet
     */
    public function __construct(AF_Model_InputSet_Primary $inputSet)
    {
        $this->inputSet = $inputSet;
    }

    /**
     * @return AF_Model_InputSet_Primary
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }
}
