<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\Context;
use User\Domain\User;

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
     * @param \User\Domain\User|null $user
     *
     * @return Entry
     */
    public function addEntry($eventName, Context $context, User $user = null)
    {
        // Value object -> clone
        $context = clone $context;

        $entry = new Entry($eventName, $context);

        if ($user) {
            $entry->setUser($user);
        }

        $this->entryRepository->add($entry);

        return $entry;
    }
}
