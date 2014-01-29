<?php

namespace Tests\AF;

use AF\Domain\AF;
use Core\Test\TestCase;
use Core_Tools;

class AFTest extends TestCase
{
    public function testConstruct()
    {
        $o = new \AF\Domain\AF(strtolower(Core_Tools::generateString(20)));
        $this->assertTrue($o instanceof AF);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param \AF\Domain\AF $o
     * @return \AF\Domain\AF
     */
    public function testLoad(\AF\Domain\AF $o)
    {
        $this->assertTrue($o instanceof \AF\Domain\AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param \AF\Domain\AF $o
     */
    public function testDelete(\AF\Domain\AF $o)
    {
        $this->assertTrue($o instanceof \AF\Domain\AF);
    }
}
