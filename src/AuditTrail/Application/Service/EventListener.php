<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Application\Service;

use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\WorkspaceContext;
use Orga\Domain\Cell;
use Orga\Domain\Service\Cell\Input\CellInputCreatedEvent;
use Orga\Domain\Service\Cell\Input\CellInputEditedEvent;

/**
 * Event listener
 */
class EventListener
{
    /**
     * @var AuditTrailService
     */
    private $auditTrailService;

    /**
     * @param AuditTrailService $auditTrailService
     */
    public function __construct(AuditTrailService $auditTrailService)
    {
        $this->auditTrailService = $auditTrailService;
    }

    /**
     * @param CellInputCreatedEvent $event
     */
    public function onInputCreated(CellInputCreatedEvent $event)
    {
        $cell = $event->getCell();

        $context = new WorkspaceContext($cell->getGranularity()->getWorkspace());
        $context->setCell($cell);

        $this->auditTrailService->addEntry($event->getName(), $context, $event->getUser());
    }

    /**
     * @param CellInputEditedEvent $event
     */
    public function onInputEdited(CellInputEditedEvent $event)
    {
        $cell = $event->getCell();

        $context = new WorkspaceContext($cell->getGranularity()->getWorkspace());
        $context->setCell($cell);

        $this->auditTrailService->addEntry($event->getName(), $context, $event->getUser());
    }
}
