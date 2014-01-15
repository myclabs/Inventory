<?php

use Core\Test\TestCase;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\Cell;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Member;
use Unit\UnitAPI;

class Techno_Test_FamilyTest extends TestCase
{
    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Family
     */
    public static function generateObject()
    {
        $family = new Family(Core_Tools::generateRef(), 'Test');
        $family->setUnit(new UnitAPI('m'));
        $family->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $family;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Family $o
     */
    public static function deleteObject(Family $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Cell::loadList() as $o) {
            $o->delete();
        }
        foreach (Member::loadList() as $o) {
            $o->delete();
        }
        foreach (Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Family::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    /**
     * Test des dimensions
     */
    public function testDimensions()
    {
        $family = new Family(Core_Tools::generateRef(), Core_Tools::generateRef());

        $this->assertNotNull($family->getDimensions());
        // Add
        $dimension1 = new Dimension($family, Core_Tools::generateRef(), Core_Tools::generateRef(), Dimension::ORIENTATION_HORIZONTAL);
        $dimension2 = new Dimension($family, Core_Tools::generateRef(), Core_Tools::generateRef(), Dimension::ORIENTATION_HORIZONTAL);
        $family->addDimension($dimension1);
        $family->addDimension($dimension2);
        $this->assertCount(2, $family->getDimensions());
        // Remove
        $family->removeDimension($dimension1);
        $this->assertCount(1, $family->getDimensions());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testCells1()
    {
        $family = new Family(Core_Tools::generateRef(), Core_Tools::generateRef());
        $family->getCell(['foo', 'bar']);
    }

    /**
     * @expectedException Core_Exception_NotFound
     */
    public function testCells2()
    {
        $family = new Family(Core_Tools::generateRef(), Core_Tools::generateRef());
        $family->getCell([]);
    }

    /**
     * Test des cellules avec 1 dimensions et 2 cellules
     */
    public function testCells1Dimension1()
    {
        $family = new Family(Core_Tools::generateRef(), Core_Tools::generateRef());
        $family->setUnit(new UnitAPI('m'));
        $family->save();
        $this->entityManager->flush();

        // 1 dimension
        $dimension1 = new Dimension($family, Core_Tools::generateRef(), 'Test', Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();

        // 1er membre
        $member1 = new Member($dimension1, Core_Tools::generateRef(), 'Member');
        $family->save();
        $this->assertAttributeCount(1, 'cells', $family);
        $this->entityManager->flush();

        // 2è membre
        $member2 = new Member($dimension1, Core_Tools::generateRef(), 'Member');
        $family->save();
        $this->assertAttributeCount(2, 'cells', $family);
        $this->entityManager->flush();

        $this->assertCount(1, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(2, 'cells', $family);
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member1]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member2]));
        $this->assertNotSame($family->getCell([$member1]), $family->getCell([$member2]));

        // Delete all
        self::deleteObject($family);
    }

    /**
     * Test des cellules avec 2 dimensions et une cellule
     */
    public function testCells2Dimensions1()
    {
        $family = self::generateObject();

        $dimension1 = new Dimension($family, Core_Tools::generateRef(), 'Test 1', Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Member($dimension1, Core_Tools::generateRef(), 'Member');

        $dimension2 = new Dimension($family, Core_Tools::generateRef(), 'Test 2', Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Member($dimension2, Core_Tools::generateRef(), 'Member');

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        // Delete all
        self::deleteObject($family);
    }

    /**
     * Test des cellules avec 2 dimensions et 4 cellules
     */
    public function testCells2Dimensions2()
    {
        $family = self::generateObject();

        $dimension1 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Member($dimension1, Core_Tools::generateRef(), 'Member');
        $member12 = new Member($dimension1, Core_Tools::generateRef(), 'Member');

        $dimension2 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Member($dimension2, Core_Tools::generateRef(), 'Member');
        $member22 = new Member($dimension2, Core_Tools::generateRef(), 'Member');

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(4, 'cells', $family);
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member12, $member21]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member11, $member22]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member12, $member22]));

        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member12, $member21]));
        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member11, $member22]));
        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member12, $member22]));

        // Delete all
        self::deleteObject($family);
    }

    /**
     * Test des cellules avec 3 dimensions
     */
    public function testCells3Dimensions()
    {
        $family = self::generateObject();

        $dimension1 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Member($dimension1, Core_Tools::generateRef(), 'Member');

        $dimension2 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_HORIZONTAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Member($dimension2, Core_Tools::generateRef(), 'Member');

        $dimension3 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_VERTICAL);
        $dimension3->save();
        $this->entityManager->flush();
        $member31 = new Member($dimension3, Core_Tools::generateRef(), 'Member');

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(3, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member11, $member21, $member31]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member31, $member21, $member11]));
        $this->assertSame(
            $family->getCell([$member11, $member21, $member31]),
            $family->getCell([$member31, $member21, $member11])
        );

        // Delete all
        self::deleteObject($family);
    }

    /**
     * Test des cellules avec 2 dimensions et l'utilisation du même ref pour les 2 dimensions
     *
     * Ce test vérifie qu'il n'y a pas de conflit/mélange dut au fait qu'on utilise le même mot-clé
     * en coordonnées dans les 2 dimensions
     */
    public function testCells2DimensionsSameRef()
    {
        $family = self::generateObject();

        $dimension1 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Member($dimension1, Core_Tools::generateRef(), 'Member');

        $dimension2 = new Dimension($family, Core_Tools::generateRef(), 'Dim', Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Member($dimension2, Core_Tools::generateRef(), 'Member');

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno\Domain\Family\Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        // Delete all
        self::deleteObject($family);
    }
}
