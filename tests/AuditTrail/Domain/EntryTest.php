<?php
/**
 * @author matthieu.napoli
 */

use AuditTrail\Domain\Context\Context;
use AuditTrail\Domain\Entry;
use Core\Test\TestCase;

/**
 * EntryTest
 */
class AuditTrail_EntryTest extends TestCase
{
    public function testDate()
    {
        /** @var Context $context */
        $context = $this->getMockForAbstractClass('AuditTrail\Domain\Context\Context');
        $entry = new Entry('foo', $context);

        $this->assertNotNull($entry->getDate());
        $this->assertLessThanOrEqual(new DateTime(), $entry->getDate());
    }
}
