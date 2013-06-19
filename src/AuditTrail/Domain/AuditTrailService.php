<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\Context;
use User_Model_User;

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
     * @param string               $eventName
     * @param Context              $context
     * @param User_Model_User|null $user
     */
    public function addEntry($eventName, Context $context, User_Model_User $user = null)
    {
        $entry = new Entry($eventName, $context);

        if ($user) {
            $entry->setUser($user);
        }

        $this->entryRepository->add($entry);
    }
}
