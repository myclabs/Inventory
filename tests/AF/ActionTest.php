<?php

namespace Tests\AF;

use AF_Model_Action;
use Core\Test\TestCase;

class ActionTest extends TestCase
{
    public function testConstruct()
    {
        /** @var $o AF_Model_Action */
        $o = $this->getMockForAbstractClass(AF_Model_Action::class);
        $this->assertTrue($o instanceof AF_Model_Action);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_Action $o
     * @return AF_Model_Action
     */
    public function testLoad(AF_Model_Action $o)
    {
        $this->assertTrue($o instanceof AF_Model_Action);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_Action $o
     */
    public function testDelete(AF_Model_Action $o)
    {
        $this->assertTrue($o instanceof AF_Model_Action);
    }

    public function testCheckConfig()
    {
        /** @var $o AF_Model_Action */
        $o = $this->getMockForAbstractClass(AF_Model_Action::class);
        $errors = $o->checkConfig();
        $this->assertCount(1, $errors);
        $this->assertTrue($errors[0]->getFatal());
        return $o;
    }
}
