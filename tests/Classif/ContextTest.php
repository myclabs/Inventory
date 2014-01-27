<?php

namespace Tests\Classif;

use Classif_Model_Context;
use Core\Test\TestCase;

class ContextTest extends TestCase
{
    /**
     * Generation de l'objet de test.
     * @param string $ref
     * @param string $label
     * @return Classif_Model_Context
     */
    public static function generateObject($ref = null, $label = null)
    {
        $o = new Classif_Model_Context();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->save();
        self::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Context $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_Context
     */
    public function testConstruct()
    {
        $o = new Classif_Model_Context();
        $o->setRef('RefContextTest');
        $o->setLabel('LabelContextTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $this->entityManager->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Context $o
     * @return Classif_Model_Context
     */
    public function testLoad(Classif_Model_Context $o)
    {
         $oLoaded = Classif_Model_Context::load($o->getKey());
         $this->assertInstanceOf(Classif_Model_Context::class, $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_Context $o
     */
    public function testDelete(Classif_Model_Context $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
