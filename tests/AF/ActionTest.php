<?php

namespace Tests\AF;

use AF\Domain\Action\Action;
use Core\Test\TestCase;

/**
 * @covers \AF\Domain\Action\Action
 */
class ActionTest extends TestCase
{
    /**
     * L'action n'est associée à aucune condition
     */
    public function testCheckConfigNoCondition()
    {
        /** @var $o Action */
        $o = $this->getMockForAbstractClass(Action::class);
        $errors = $o->checkConfig();
        $this->assertCount(1, $errors);
        $this->assertTrue($errors[0]->getFatal());
    }
}
