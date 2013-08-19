<?php
/**
 * @author matthieu.napoli
 */

use User\Event\UserEvent;
use User\Event\UserEventTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event: une saisie d'une cellule a été modifiée
 */
class Orga_Service_InputEditedEvent extends Event implements UserEvent
{
    use UserEventTrait;

    const NAME = 'orga.inputEdited';

    /**
     * @var Orga_Model_Cell
     */
    private $cell;

    /**
     * @param Orga_Model_Cell $cell
     */
    public function __construct(Orga_Model_Cell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getCell()
    {
        return $this->cell;
    }
}
