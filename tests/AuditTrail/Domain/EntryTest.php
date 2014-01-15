<?php

namespace Tests\AuditTrail\Domain;

use AuditTrail\Domain\Context\Context;
use AuditTrail\Domain\Entry;
use Core\Test\TestCase;
use DateTime;

class EntryTest extends TestCase
{
    public function testDate()
    {
        /** @var Context $context */
        $context = $this->getMockForAbstractClass(Context::class);
        $entry = new Entry('foo', $context);

        $this->assertNotNull($entry->getDate());
        $this->assertLessThanOrEqual(new DateTime(), $entry->getDate());
    }
}
