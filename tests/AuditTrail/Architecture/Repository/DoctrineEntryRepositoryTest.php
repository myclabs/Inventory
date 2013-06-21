<?php
/**
 * @author matthieu.napoli
 */

use AuditTrail\Architecture\Repository\DoctrineEntryRepository;
use AuditTrail\Domain\Context\GlobalContext;
use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\Entry;

/**
 * EntryRepositoryTest tests
 */
class AuditTrail_DoctrineEntryRepositoryTest extends Core_Test_TestCase
{
    /**
     * @var DoctrineEntryRepository
     */
    private $entryRepository;

    public function testFindLatest()
    {
        $this->markTestIncomplete("TODO");
        $entry1 = new Entry('foo', new GlobalContext());
        $entry2 = new Entry('bar', new GlobalContext());

        $this->entryRepository->add($entry1);
        $this->entryRepository->add($entry2);
        $this->entityManager->flush();

        $entries = $this->entryRepository->findLatest(1);

        $this->entryRepository->remove($entry1);
        $this->entryRepository->remove($entry2);
        $this->entityManager->flush();

        $this->assertCount(1, $entries);
        $this->assertSame($entry2, current($entries));
    }

    public function testFindLatestForOrganization()
    {
        $this->markTestIncomplete("TODO");
        $organization = new Orga_Model_Organization();
        $organization->save();

        $entry1 = new Entry('foo', new OrganizationContext($organization));
        $entry2 = new Entry('bar', new OrganizationContext($organization));
        $entry3 = new Entry('bam', new GlobalContext());

        $this->entryRepository->add($entry1);
        $this->entryRepository->add($entry2);
        $this->entryRepository->add($entry3);
        $this->entityManager->flush();

        $entries = $this->entryRepository->findLatestForOrganizationContext(new OrganizationContext($organization), 1);

        $this->entryRepository->remove($entry1);
        $this->entryRepository->remove($entry2);
        $this->entryRepository->remove($entry3);
        $this->entityManager->flush();

        $this->assertCount(1, $entries);
        $this->assertSame($entry2, current($entries));
    }


    public function setUp()
    {
        parent::setUp();

        $this->entryRepository = $this->get('AuditTrail\Domain\EntryRepository');

        // Vide la table si elle contient d'anciennes entrées
        foreach ($this->entryRepository->findAll() as $entry) {
            $this->entryRepository->remove($entry);
        }
        $this->entityManager->flush();
    }
}
