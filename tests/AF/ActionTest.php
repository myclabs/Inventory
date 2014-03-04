<?php

namespace Tests\AF;

use AF\Domain\Action\Action;
use Core\Test\TestCase;

class ActionTest extends TestCase
{
    public function testConstruct()
    {
        /** @var $o Action */
        $o = $this->getMockForAbstractClass(Action::class);
        $this->assertTrue($o instanceof Action);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Action $o
     * @return Action
     */
    public function testLoad(Action $o)
    {
        $this->assertTrue($o instanceof Action);
        return $o;
    }

    /**
     * @depends testLoad
     * @param Action $o
     */
    public function testDelete(Action $o)
    {
        $this->assertTrue($o instanceof Action);
    }

    public function testCheckConfig()
    {
        /** @var $o Action */
        $o = $this->getMockForAbstractClass(Action::class);
        $errors = $o->checkConfig();
        $this->assertCount(1, $errors);
        $this->assertTrue($errors[0]->getFatal());
        return $o;
    }
}
