<?php
/**
 * @author matthieu.napoli
 */

use AuditTrail\Domain\AuditTrailService;
use AuditTrail\Domain\Context\Context;
use AuditTrail\Domain\EntryRepository;

/**
 * AuditTrailService tests
 */
class AuditTrail_AuditTrailServiceTest extends Core_Test_TestCase
{
    public function testAddEntry()
    {
        /** @var Context $context */
        $context = $this->getMockForAbstractClass('AuditTrail\Domain\Context\Context');
        $metadata = $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', [], ['AuditTrail\Domain\Entry']);

        /** @var PHPUnit_Framework_MockObject_MockObject|EntryRepository $repository */
        $repository = $this->getMock('AuditTrail\Domain\EntryRepository', [], [null, $metadata]);
        $repository->expects($this->once())
            ->method('add')
            ->with($this->attributeEqualTo('eventName', 'foo'));

        $service = new AuditTrailService($repository);
        $service->addEntry('foo', $context);
    }
}
