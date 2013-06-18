<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Application\Service;

use AF_Service_InputEditedEvent;
use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\OrganizationContext;
use Core_Exception_NotFound;
use Orga_Model_Cell;

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
     * @param AF_Service_InputEditedEvent $event
     */
    public function onInputEdited(AF_Service_InputEditedEvent $event)
    {
        $inputSet = $event->getInputSet();
        try {
            $cell = Orga_Model_Cell::loadByAFInputSetPrimary($inputSet);
        } catch (Core_Exception_NotFound $e) {
            return;
        }

        $context = new OrganizationContext($cell->getGranularity()->getProject());

        $this->auditTrailService->addEntry($event->getName(), $context);
    }
}
