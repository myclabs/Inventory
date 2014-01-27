<?php

namespace Tests\AF;

use AF_Model_AF;
use Core\Test\TestCase;
use Core_Tools;

class AFTest extends TestCase
{
    public function testConstruct()
    {
        $o = new AF_Model_AF(strtolower(Core_Tools::generateString(20)));
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_AF $o
     * @return AF_Model_AF
     */
    public function testLoad(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_AF $o
     */
    public function testDelete(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
    }
}
