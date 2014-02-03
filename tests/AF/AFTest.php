<?php

namespace Tests\AF;

use AF\Domain\AF;
use Core\Test\TestCase;
use Core_Tools;

class AFTest extends TestCase
{
    public function testConstruct()
    {
        $o = new AF(strtolower(Core_Tools::generateString(20)));
        $this->assertTrue($o instanceof AF);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF $o
     * @return AF
     */
    public function testLoad(AF $o)
    {
        $this->assertTrue($o instanceof AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF $o
     */
    public function testDelete(AF $o)
    {
        $this->assertTrue($o instanceof AF);
    }
}
