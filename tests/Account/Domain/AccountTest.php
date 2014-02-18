<?php

namespace Tests\Account\Domain;

use Account\Domain\Account;
use Core\Test\TestCase;

/**
 * @covers \Account\Domain\Account
 */
class ActionTest extends TestCase
{
    public function testRename()
    {
        $account = new Account('foo');
        $this->assertEquals('foo', $account->getName());
        $account->rename('bar');
        $this->assertEquals('bar', $account->getName());
    }
}
