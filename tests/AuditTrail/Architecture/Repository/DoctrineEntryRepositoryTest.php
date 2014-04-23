<?php

namespace Tests\AuditTrail\Architecture\Repository;

use Account\Architecture\Repository\DoctrineAccountRepository;
use Account\Domain\Account;
use AuditTrail\Architecture\Repository\DoctrineEntryRepository;
use AuditTrail\Domain\Context\GlobalContext;
use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core\Test\TestCase;
use Orga_Model_Axis;
use Orga_Model_Granularity;
use Orga_Model_Member;
use Orga_Model_Organization;
use Orga_Service_OrganizationService;

class DoctrineEntryRepositoryTest extends TestCase
{
    /**
     * @var DoctrineEntryRepository
     */
    private $entryRepository;

    /**
     * @var DoctrineAccountRepository
     */
    private $accountRepository;

    /**
     * @Inject
     * @var Orga_Service_OrganizationService
     */
    private $organizationService;

    public function testFindLatest()
    {
        $entry1 = new Entry('foo', new GlobalContext());
        // Sleep 1 seconde pour que le tri par date fonctionne
        sleep(1);
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
        $account = new Account('test');
        $this->accountRepository->add($account);
        $this->entityManager->flush();
        $organization = new Orga_Model_Organization($account);
        $organization->save();

        $entry1 = new Entry('foo', new OrganizationContext($organization));
        // Sleep 1 seconde pour que le tri par date fonctionne
        sleep(1);
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
        $this->organizationService->deleteOrganization($organization);

        $this->assertCount(1, $entries);
        $this->assertSame($entry2, current($entries));
    }

    public function testFindLatestForCell()
    {
        $account = new Account('test');
        $this->accountRepository->add($account);
        $this->entityManager->flush();
        $organization = new Orga_Model_Organization($account);
        $axis = new Orga_Model_Axis($organization, 'axis');
        $axis->setLabel('axis');
        $member = new Orga_Model_Member($axis, 'member');
        $member->setLabel('member');
        $organization->save();
        $this->entityManager->flush();
        new Orga_Model_Granularity($organization, [$axis]);
        $organization->save();
        $this->entityManager->flush();

        $cell = $organization->getOrderedGranularities()[0]->getOrderedCells()[0];

        $organizationContext = new OrganizationContext($organization);
        $organizationContext->setCell($cell);
        $entry1 = new Entry('foo', $organizationContext);
        // Sleep 1 seconde pour que le tri par date fonctionne
        sleep(1);
        $organizationContext = new OrganizationContext($organization);
        $organizationContext->setCell($cell);
        $entry2 = new Entry('bar', $organizationContext);
        $entry3 = new Entry('bam', new GlobalContext());
        $entry4 = new Entry('bim', new OrganizationContext($organization));

        $this->entryRepository->add($entry1);
        $this->entryRepository->add($entry2);
        $this->entryRepository->add($entry3);
        $this->entryRepository->add($entry4);
        $this->entityManager->flush();

        $entries = $this->entryRepository->findLatestForOrganizationContext($organizationContext, 1);

        $this->entryRepository->remove($entry1);
        $this->entryRepository->remove($entry2);
        $this->entryRepository->remove($entry3);
        $this->entryRepository->remove($entry4);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->organizationService->deleteOrganization(Orga_Model_Organization::load($organization->getId()));
        $this->entityManager->flush();

        $this->assertCount(1, $entries);
        $this->assertSame($entry2, current($entries));
    }


    public function setUp()
    {
        parent::setUp();

        $this->entryRepository = $this->get(EntryRepository::class);
        $this->accountRepository = $this->entityManager->getRepository(Account::class);

        // Vide la table si elle contient d'anciennes entrées
        foreach ($this->entryRepository->findAll() as $entry) {
            $this->entryRepository->remove($entry);
        }
        $this->entityManager->flush();
    }


    public function tearDown()
    {
        foreach ($this->accountRepository->findAll() as $account) {
            $this->accountRepository->remove($account);
        }
        $this->entityManager->flush();
    }
}
