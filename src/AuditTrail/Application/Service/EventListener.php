<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Application\Service;

use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\OrganizationContext;
use Core_Exception_NotFound;
use Orga_Model_Cell;
use Orga_Service_InputCreatedEvent;
use Orga_Service_InputEditedEvent;
use User\Domain\User;

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
     * @param Orga_Service_InputCreatedEvent $event
     */
    public function onInputCreated(Orga_Service_InputCreatedEvent $event)
    {
        $cell = $event->getCell();

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);

        $this->auditTrailService->addEntry($event->getName(), $context, $event->getUser());
    }

    /**
     * @param Orga_Service_InputEditedEvent $event
     */
    public function onInputEdited(Orga_Service_InputEditedEvent $event)
    {
        $cell = $event->getCell();

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);

        $this->auditTrailService->addEntry($event->getName(), $context, $event->getUser());
    }
}
