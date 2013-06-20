<?php
/**
 * @author matthieu.napoli
 */

use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\Context;
use AuditTrail\Domain\Context\GlobalContext;
use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\EntryRepository;
use Doctrine\ORM\EntityManager;

/**
 * EntryRepositoryTest tests
 */
class AuditTrail_DoctrineEntryRepositoryTest extends Core_Test_TestCase
{
    /**
     * @var AuditTrailService
     */
    private $auditTrailService;
    /**
     * @var EntryRepository
     */
    private $entryRepository;

    public function testFindLatest()
    {
        $context = new GlobalContext();

        $entry1 = $this->auditTrailService->addEntry('foo', $context);
        $entry2 = $this->auditTrailService->addEntry('bar', $context);

        $entries = $this->entryRepository->findLatest(1);

        $this->assertCount(1, $entries);
        $this->assertContains($entry2, $entries);
    }

    public function testFindLatestForOrganizationContext()
    {
        $organization = new Orga_Model_Organization();
        $organizationContext = new OrganizationContext($organization);
        $globalContext = new GlobalContext();

        $entry1 = $this->auditTrailService->addEntry('foo', $organizationContext);
        $entry2 = $this->auditTrailService->addEntry('bar', $organizationContext);
        $entry3 = $this->auditTrailService->addEntry('bam', $globalContext);

        $entries = $this->entryRepository->findLatestForOrganizationContext($organizationContext, 1);

        $this->assertCount(1, $entries);
        $this->assertContains($entry2, $entries);
    }


    public function setUp()
    {
        parent::setUp();

        $this->auditTrailService = $this->get('AuditTrail\Domain\AuditTrailService');
        $this->entryRepository = $this->get('AuditTrail\Domain\EntryRepository');

        // Vide la table si elle contient d'anciennes entrées
        foreach ($this->entryRepository->findAll() as $entry) {
            $this->entryRepository->remove($entry);
        }
        $this->entityManager->flush();
    }
}
