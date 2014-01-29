<?php

namespace Tests\AF;

use AF\Domain\Action\Action;
use Core\Test\TestCase;

class ActionTest extends TestCase
{
    public function testConstruct()
    {
        /** @var $o \AF\Domain\Action\Action */
        $o = $this->getMockForAbstractClass(Action\Action::class);
        $this->assertTrue($o instanceof Action\Action);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param \AF\Domain\Action\Action $o
     * @return \AF\Domain\Action\Action
     */
    public function testLoad(Action\Action $o)
    {
        $this->assertTrue($o instanceof Action\Action);
        return $o;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\Action\Action $o
     */
    public function testDelete(Action\Action $o)
    {
        $this->assertTrue($o instanceof Action\Action);
    }

    public function testCheckConfig()
    {
        /** @var $o \AF\Domain\Action\Action */
        $o = $this->getMockForAbstractClass(Action\Action::class);
        $errors = $o->checkConfig();
        $this->assertCount(1, $errors);
        $this->assertTrue($errors[0]->getFatal());
        return $o;
    }
}
