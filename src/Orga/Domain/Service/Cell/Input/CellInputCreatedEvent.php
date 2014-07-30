<?php
/**
 * @author matthieu.napoli
 */

namespace Orga\Domain\Service\Cell\Input;

use Orga\Domain\Cell;
use User\Domain\Event\UserEvent;
use User\Domain\Event\UserEventTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event: une saisie d'une cellule a été remplie
 */
class CellInputCreatedEvent extends Event implements UserEvent
{
    use UserEventTrait;

    const NAME = 'orga.inputCreated';

    /**
     * @var Cell
     */
    private $cell;

    public function __construct(Cell $cell)
    {
        $this->cell = $cell;
    }

    /**
     * @return Cell
     */
    public function getCell()
    {
        return $this->cell;
    }
}
