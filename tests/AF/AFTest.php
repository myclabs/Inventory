<?php

namespace Tests\AF;

use AF\Domain\AF\AF;
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
     * @param \AF\Domain\AF\AF $o
     * @return \AF\Domain\AF\AF
     */
    public function testLoad(AF $o)
    {
        $this->assertTrue($o instanceof AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\AF\AF $o
     */
    public function testDelete(AF $o)
    {
        $this->assertTrue($o instanceof AF);
    }
}
