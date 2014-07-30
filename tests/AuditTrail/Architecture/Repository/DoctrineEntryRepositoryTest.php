<?php

namespace Tests\AuditTrail\Architecture\Repository;

use Account\Architecture\Repository\DoctrineAccountRepository;
use Account\Domain\Account;
use AuditTrail\Architecture\Repository\DoctrineEntryRepository;
use AuditTrail\Domain\Context\GlobalContext;
use AuditTrail\Domain\Context\WorkspaceContext;
use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core\Test\TestCase;
use Orga\Domain\Axis;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;
use Orga\Application\Service\Workspace\WorkspaceService;

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
     * @var WorkspaceService
     */
    private $workspaceService;

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

    public function testFindLatestForWorkspace()
    {
        $account = new Account('test');
        $this->accountRepository->add($account);
        $this->entityManager->flush();
        $workspace = new Workspace($account);
        $workspace->save();

        $entry1 = new Entry('foo', new WorkspaceContext($workspace));
        // Sleep 1 seconde pour que le tri par date fonctionne
        sleep(1);
        $entry2 = new Entry('bar', new WorkspaceContext($workspace));
        $entry3 = new Entry('bam', new GlobalContext());

        $this->entryRepository->add($entry1);
        $this->entryRepository->add($entry2);
        $this->entryRepository->add($entry3);
        $this->entityManager->flush();

        $entries = $this->entryRepository->findLatestForWorkspaceContext(new WorkspaceContext($workspace), 1);

        $this->entryRepository->remove($entry1);
        $this->entryRepository->remove($entry2);
        $this->entryRepository->remove($entry3);
        $this->entityManager->flush();
        $this->workspaceService->delete($workspace);

        $this->assertCount(1, $entries);
        $this->assertSame($entry2, current($entries));
    }

    public function testFindLatestForCell()
    {
        $account = new Account('test');
        $this->accountRepository->add($account);
        $this->entityManager->flush();
        $workspace = new Workspace($account);
        $axis = new Axis($workspace, 'axis');
        $axis->getLabel()->set('axis', 'fr');
        $member = new Member($axis, 'member');
        $member->getLabel()->set('member', 'fr');
        $workspace->save();
        $this->entityManager->flush();
        new Granularity($workspace, [$axis]);
        $workspace->save();
        $this->entityManager->flush();

        $cell = $workspace->getOrderedGranularities()[0]->getOrderedCells()[0];

        $workspaceContext = new WorkspaceContext($workspace);
        $workspaceContext->setCell($cell);
        $entry1 = new Entry('foo', $workspaceContext);
        // Sleep 1 seconde pour que le tri par date fonctionne
        sleep(1);
        $workspaceContext = new WorkspaceContext($workspace);
        $workspaceContext->setCell($cell);
        $entry2 = new Entry('bar', $workspaceContext);
        $entry3 = new Entry('bam', new GlobalContext());
        $entry4 = new Entry('bim', new WorkspaceContext($workspace));

        $this->entryRepository->add($entry1);
        $this->entryRepository->add($entry2);
        $this->entryRepository->add($entry3);
        $this->entryRepository->add($entry4);
        $this->entityManager->flush();

        $entries = $this->entryRepository->findLatestForWorkspaceContext($workspaceContext, 1);

        $this->entryRepository->remove($entry1);
        $this->entryRepository->remove($entry2);
        $this->entryRepository->remove($entry3);
        $this->entryRepository->remove($entry4);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->workspaceService->delete(Workspace::load($workspace->getId()));
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
