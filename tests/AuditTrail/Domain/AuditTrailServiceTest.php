<?php

namespace Tests\AuditTrail\Domain;

use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\Context;
use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core\Test\TestCase;

class AuditTrailServiceTest extends TestCase
{
    public function testAddEntry()
    {
        /** @var Context $context */
        $context = $this->getMockForAbstractClass(Context::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|EntryRepository $repository */
        $repository = $this->getMockForAbstractClass(EntryRepository::class);
        $repository->expects($this->once())
            ->method('add')
            ->with($this->attributeEqualTo('eventName', 'foo'));

        $service = new AuditTrailService($repository);
        $entry = $service->addEntry('foo', $context);

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('foo', $entry->getEventName());
    }
}
