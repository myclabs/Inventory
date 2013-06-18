<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\Context;

/**
 * Audit trail service
 */
class AuditTrailService
{
    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @param EntryRepository $entryRepository
     */
    public function __construct(EntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    /**
     * @param string  $eventName
     * @param Context $context
     */
    public function addEntry($eventName, Context $context)
    {
        $entry = new Entry($eventName, $context);

        $this->entryRepository->add($entry);
    }
}
