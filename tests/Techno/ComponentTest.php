<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */
use Techno\Domain\Family\Family;
use Techno\Domain\Component;
use Unit\UnitAPI;

/**
 * @package Techno
 */
class Techno_Test_ComponentTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test de la documentation
     */
    public function testDocumentation()
    {
        /** @var $o Family */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $o->setDocumentation("Documentation");
        $this->assertEquals("Documentation", $o->getDocumentation());
    }

    /**
     * Test de baseUnit
     */
    public function testBaseUnit1()
    {
        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $baseUnit = new UnitAPI('m');
        $o->setBaseUnit($baseUnit);
        $this->assertSame($baseUnit, $o->getBaseUnit());
    }

    /**
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testBaseUnit2()
    {
        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $o->getBaseUnit();
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testUnit1()
    {
        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $o->setBaseUnit(new UnitAPI('m'));
        $o->setUnit(new UnitAPI('g'));
    }

    /**
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testUnit2()
    {
        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $o->setUnit(new UnitAPI('g'));
    }

    /**
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testUnit3()
    {
        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $o->getUnit();
    }

    /**
     * Test des tags
     */
    public function testTags()
    {
        $tag1 = Techno_Test_TagTest::generateObject();
        $tag2 = Techno_Test_TagTest::generateObject();

        /** @var $o Component */
        $o = $this->getMockForAbstractClass('Techno\Domain\Component');
        $this->assertNotNull($o->getTags());
        // Add
        $o->addTag($tag1);
        $o->addTag($tag2);
        $this->assertCount(2, $o->getTags());
        // Has tag
        foreach ($o->getTags() as $tag) {
            $this->assertTrue($o->hasTag($tag));
        }
        // Remove
        $o->removeTag($tag1);
        $this->assertCount(1, $o->getTags());
        // Delete all
        Techno_Test_TagTest::deleteObject($tag1);
        Techno_Test_TagTest::deleteObject($tag2);
    }

}
