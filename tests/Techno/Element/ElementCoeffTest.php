<?php

use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Component;
use Techno\Domain\Element\CoeffElement;
use Unit\UnitAPI;

class Techno_Test_Element_CoeffTest extends Core_Test_TestCase
{
    /**
     * Méthode appelée avant les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Component::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return CoeffElement
     */
    public function testConstruct()
    {
        // Fixtures
        $value = new Calc_Value(10, 20);
        $baseUnit = new UnitAPI('m');
        $unit = new UnitAPI('km');

        $o = new CoeffElement();

        $this->assertInstanceOf('Calc_Value', $o->getValue());

        $o->setValue($value);
        $o->setBaseUnit($baseUnit);
        $o->setUnit($unit);
        $o->setDocumentation("Documentation");

        $o->save();
        $this->entityManager->flush();

        $this->assertNotEmpty($o->getKey());
        $this->assertSame($value, $o->getValue());
        $this->assertSame($baseUnit, $o->getBaseUnit());
        $this->assertSame($unit, $o->getUnit());
        $this->assertEquals("Documentation", $o->getDocumentation());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param CoeffElement $o
     * @return CoeffElement
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Component');
        /** @var $oLoaded CoeffElement */
        $oLoaded = CoeffElement::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Element\CoeffElement', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getValue
        $this->assertEquals($o->getValue(), $oLoaded->getValue());
        $this->assertNotSame($o->getValue(), $oLoaded->getValue());
        // getBaseUnit
        $this->assertEquals($o->getBaseUnit(), $oLoaded->getBaseUnit());
        $this->assertNotSame($o->getBaseUnit(), $oLoaded->getBaseUnit());
        // getUnit
        $this->assertEquals($o->getUnit(), $oLoaded->getUnit());
        $this->assertNotSame($o->getUnit(), $oLoaded->getUnit());
        // Documentation
        $this->assertEquals("Documentation", $o->getDocumentation());
        return $oLoaded;
    }

    /**
     * @param CoeffElement $o
     * @depends testLoad
     */
    public function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}
