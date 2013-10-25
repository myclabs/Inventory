<?php
/**
 * @author matthieu.napoli
 */

use User\Domain\Event\UserEvent;
use User\Domain\Event\UserEventTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event: une saisie d'une cellule a été remplie
 */
class Orga_Service_InputCreatedEvent extends Event implements UserEvent
{
    use \User\Domain\Event\UserEventTrait;

    const NAME = 'orga.inputCreated';

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
