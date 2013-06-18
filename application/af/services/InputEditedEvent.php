<?php
/**
 * @author matthieu.napoli
 */

use Symfony\Component\EventDispatcher\Event;

/**
 * Event: an input was edited
 */
class AF_Service_InputEditedEvent extends Event
{
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
