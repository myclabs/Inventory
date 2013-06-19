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
use User_Model_User;

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
     * Utilisateur connecté
     *
     * @var User_Model_User|null
     */
    private $user;

    /**
     * @param AuditTrailService $auditTrailService
     * @param User_Model_User|null $user
     */
    public function __construct(AuditTrailService $auditTrailService, $user)
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
        $context->setCell($cell);

        $this->auditTrailService->addEntry($event->getName(), $context, $this->user);
    }
}
