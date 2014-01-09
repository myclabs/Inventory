<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Family;

class Techno_Test_Family_DimensionTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Techno_Test_Family_DimensionSetUp');
        $suite->addTestSuite('Techno_Test_Family_DimensionMetier');
        return $suite;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Dimension
     */
    public static function generateObject()
    {
        // Fixtures
        $family = Techno_Test_FamilyTest::generateObject();
        $o = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Dimension $o
     */
    public static function deleteObject($o)
    {
        Techno_Test_FamilyTest::deleteObject($o->getFamily());
        \Core\ContainerSingleton::getEntityManager()->flush();
    }
}

class Techno_Test_Family_DimensionSetUp extends TestCase
{
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Family::loadList() as $o) {
            $o->delete();
        }
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * @return Dimension
     */
    public function testConstruct()
    {
        // Fixtures
        $family = Techno_Test_FamilyTest::generateObject();

        $o = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);

        $this->assertSame($family, $o->getFamily());
        $this->assertEquals(Dimension::ORIENTATION_HORIZONTAL, $o->getOrientation());

        $o->save();
        $this->entityManager->flush();

        $this->assertInstanceOf('Techno\Domain\Family\Family', $o->getFamily());
        $this->assertEquals($family->getRef(), $o->getFamily()->getRef());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Dimension $o
     * @return Dimension
     */
    public function testLoad($o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Dimension */
        $oLoaded = Dimension::load($o->getKey());

        $this->assertInstanceOf('Techno\Domain\Family\Dimension', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        // getFamily
        $this->assertInstanceOf('Techno\Domain\Family\Family', $oLoaded->getFamily());
        $this->assertEquals($o->getFamily()->getRef(), $oLoaded->getFamily()->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Dimension $o
     */
    public function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        // Remove from the family to avoid cascade problems
        $o->getFamily()->removeDimension($o);
        // Delete fixtures
        Techno_Test_FamilyTest::deleteObject($o->getFamily());
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}

class Techno_Test_Family_DimensionMetier extends TestCase
{
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Family::loadList() as $o) {
            $o->delete();
        }
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * Teste l'association à sa famille
     */
    public function testBidirectionalFamilyAssociation()
    {
        // Fixtures
        $family = Techno_Test_FamilyTest::generateObject();

        // Charge la collection pour éviter le lazy-loading en dessous
        // (le lazy loading entrainerait le chargement depuis la BDD et donc la prise en compte
        // de l'association BDD même si elle n'était pas faite au niveau PHP)
        $family->getDimensions();

        $o = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);

        // Vérifie que l'association a été affectée bidirectionnellement
        $this->assertTrue($family->hasDimension($o));

        Techno_Test_FamilyTest::deleteObject($family);
    }

    /**
     * Teste la persistence en cascade depuis la famille
     */
    public function testCascadeFromFamily()
    {
        // Fixtures
        $family = Techno_Test_FamilyTest::generateObject();

        $o = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);

        // Vérification de la cascade de la persistence
        $family->save();
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_MANAGED, $this->entityManager->getUnitOfWork()->getEntityState($o));

        // Vérification de la cascade de la suppression
        Techno_Test_FamilyTest::deleteObject($family);
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

    /**
     * Test de la position
     */
    public function testPosition()
    {
        // Fixtures
        $family = Techno_Test_FamilyTest::generateObject();

        $o1 = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);
        $o1->save();
        $o2 = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_HORIZONTAL);
        $o2->save();
        $o3 = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_VERTICAL);
        $o3->save();
        $o4 = new Dimension($family, Core_Tools::generateRef(), 'Dimension', Dimension::ORIENTATION_VERTICAL);
        $o4->save();
        $this->entityManager->flush();

        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // setPosition
        $o2->setPosition(1);
        $o2->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // up
        $o1->goUp();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(2, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // down
        $o1->goDown();
        $o1->save();
        $this->entityManager->flush();
        $this->assertEquals(2, $o1->getPosition());
        $this->assertEquals(1, $o2->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        // Delete
        $o2->delete();
        $this->assertEquals(1, $o1->getPosition());
        $this->assertEquals(1, $o3->getPosition());
        $this->assertEquals(2, $o4->getPosition());
        Techno_Test_FamilyTest::deleteObject($family);
    }
}
