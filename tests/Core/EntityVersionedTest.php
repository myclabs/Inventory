<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\LoggableListener;
use Inventory_Model_Versioned;

class EntityVersionedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        /** @var $repository \Gedmo\Loggable\Entity\Repository\LogEntryRepository */
        $repository = $this->entityManager->getRepository(LogEntry::class);
        foreach ($repository->findAll() as $version) {
            $this->entityManager->remove($version);
        }
        $this->entityManager->flush();

        /** @var \Gedmo\Loggable\LoggableListener $loggableListener */
        $loggableListener = $this->get(LoggableListener::class);
        $loggableListener->setUsername('foo');
    }

    public function testSimpleField()
    {
        /** @var $repository \Gedmo\Loggable\Entity\Repository\LogEntryRepository */
        $repository = $this->entityManager->getRepository(LogEntry::class);

        $o = new Inventory_Model_Versioned();
        $o->setName('foo');
        $o->save();
        $this->entityManager->flush();

        $o->setName('bar');
        $o->save();
        $this->entityManager->flush();

        /** @var LogEntry[] $versions */
        $versions = $repository->getLogEntries($o);

        $this->assertCount(2, $versions);

        $names = [
            1 => 'foo',
            2 => 'bar',
        ];

        foreach ($versions as $version) {
            $this->assertEquals('foo', $version->getUsername());
            $repository->revert($o, $version->getVersion());
            $this->assertEquals($names[$version->getVersion()], $o->getName());
        }

        $o->delete();
        $this->entityManager->flush();
    }
}
