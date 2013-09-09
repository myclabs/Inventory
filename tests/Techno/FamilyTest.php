<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */
use Keyword\Application\Service\KeywordService;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\Keyword;

/**
 * @package Techno
 */
class Techno_Test_FamilyTest extends Core_Test_TestCase
{

    /**
     * @var KeywordService
     */
    private $keywordService;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Techno_Model_Component::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Tag::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Meaning::loadList() as $o) {
            $o->delete();
        }
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $entityManager->getRepository('\Keyword\Domain\Keyword');
        if ($keywordRepository->count() > 0) {
            foreach ($keywordRepository->getAll() as $o) {
                $keywordRepository->remove($o);
            }
        }
        foreach (Techno_Model_Family_Cell::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Family_Member::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Family_Dimension::loadList() as $o) {
            $o->delete();
        }
        foreach (Techno_Model_Family::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    public function setUp()
    {
        parent::setUp();
        $container = Zend_Registry::get('container');
        $this->keywordService = $container->get('\Keyword\Application\Service\KeywordService');
    }

    /**
     * Test du ref
     */
    public function testRef()
    {
        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $o->setRef("test");
        $this->assertEquals("test", $o->getRef());
    }

    /**
     * Test du label
     */
    public function testLabel()
    {
        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $o->setLabel("Label");
        $this->assertEquals("Label", $o->getLabel());
    }

    /**
     * Test des tags
     */
    public function testCellsCommonTags()
    {
        $tag1 = Techno_Test_TagTest::generateObject();
        $tag2 = Techno_Test_TagTest::generateObject();

        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $this->assertNotNull($o->getCellsCommonTags());
        // Add
        $o->addCellsCommonTag($tag1);
        $o->addCellsCommonTag($tag2);
        $this->assertCount(2, $o->getCellsCommonTags());
        // Has tag
        foreach ($o->getCellsCommonTags() as $tag) {
            $this->assertTrue($o->hasCellsCommonTag($tag));
        }
        // Remove
        $o->removeCellsCommonTag($tag1);
        $this->assertCount(1, $o->getCellsCommonTags());
        // Delete all
        Techno_Test_TagTest::deleteObject($tag1);
        Techno_Test_TagTest::deleteObject($tag2);
    }

    /**
     * Test des dimensions
     */
    public function testDimensions()
    {
        $dimension1 = Techno_Test_Family_DimensionTest::generateObject();
        $dimension2 = Techno_Test_Family_DimensionTest::generateObject();

        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $this->assertNotNull($o->getDimensions());
        // Add
        $o->addDimension($dimension1);
        $o->addDimension($dimension2);
        $this->assertCount(2, $o->getDimensions());
        // Remove
        $o->removeDimension($dimension1);
        $this->assertCount(1, $o->getDimensions());
        // Delete all
        Techno_Test_Family_DimensionTest::deleteObject($dimension1);
        Techno_Test_Family_DimensionTest::deleteObject($dimension2);
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testCells1()
    {
        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $o->getCell(['foo', 'bar']);
    }

    /**
     * @expectedException Core_Exception_NotFound
     */
    public function testCells2()
    {
        /** @var $o Techno_Model_Family */
        $o = $this->getMockForAbstractClass('Techno_Model_Family');
        $o->getCell([]);
    }

    /**
     * Test des cellules avec 1 dimensions et 2 cellules
     */
    public function testCells1Dimension1()
    {
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $this->entityManager->flush();

        $family = Techno_Test_Family_CoeffTest::generateObject();

        // 1 dimension
        $dimension1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();

        // 1er membre
        $member1 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef1));
        $family->save();
        $this->assertAttributeCount(1, 'cells', $family);
        $this->entityManager->flush();

        // 2è membre
        $member2 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef2));
        $family->save();
        $this->assertAttributeCount(2, 'cells', $family);
        $this->entityManager->flush();

        $this->assertCount(1, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(2, 'cells', $family);
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member1]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member2]));
        $this->assertNotSame($family->getCell([$member1]), $family->getCell([$member2]));

        // Delete all
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $meaning1->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $this->entityManager->flush();
    }

    /**
     * Test des cellules avec 2 dimensions et une cellule
     */
    public function testCells2Dimensions1()
    {
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $meaning2 = Techno_Test_MeaningTest::generateObject();
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $this->entityManager->flush();

        $family = Techno_Test_Family_CoeffTest::generateObject();

        $dimension1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef1));

        $dimension2 = new Techno_Model_Family_Dimension($family, $meaning2,
                                                        Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Techno_Model_Family_Member($dimension2, $this->keywordService->get($keywordRef2));

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        // Delete all
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $meaning1->delete();
        $meaning2->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $this->entityManager->flush();
    }

    /**
     * Test des cellules avec 2 dimensions et 4 cellules
     */
    public function testCells2Dimensions2()
    {
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $meaning2 = Techno_Test_MeaningTest::generateObject();
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        $keywordRef3 = strtolower(Core_Tools::generateString(10));
        $keywordRef4 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef3, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef4, 'Label'));
        $this->entityManager->flush();

        $family = Techno_Test_Family_CoeffTest::generateObject();

        $dimension1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef1));
        $member12 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef2));

        $dimension2 = new Techno_Model_Family_Dimension($family, $meaning2,
                                                        Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Techno_Model_Family_Member($dimension2, $this->keywordService->get($keywordRef3));
        $member22 = new Techno_Model_Family_Member($dimension2, $this->keywordService->get($keywordRef4));

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(4, 'cells', $family);
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member12, $member21]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member11, $member22]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member12, $member22]));

        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member12, $member21]));
        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member11, $member22]));
        $this->assertNotSame($family->getCell([$member11, $member21]), $family->getCell([$member12, $member22]));

        // Delete all
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $meaning1->delete();
        $meaning2->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef3));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef4));
        $this->entityManager->flush();
    }

    /**
     * Test des cellules avec 3 dimensions
     */
    public function testCells3Dimensions()
    {
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $meaning2 = Techno_Test_MeaningTest::generateObject();
        $meaning3 = Techno_Test_MeaningTest::generateObject();
        $keywordRef1 = strtolower(Core_Tools::generateString(10));
        $keywordRef2 = strtolower(Core_Tools::generateString(10));
        $keywordRef3 = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef1, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef2, 'Label'));
        $keywordRepository->add(new Keyword($keywordRef3, 'Label'));
        $this->entityManager->flush();

        $family = Techno_Test_Family_CoeffTest::generateObject();

        $dimension1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef1));

        $dimension2 = new Techno_Model_Family_Dimension($family, $meaning2,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Techno_Model_Family_Member($dimension2, $this->keywordService->get($keywordRef2));

        $dimension3 = new Techno_Model_Family_Dimension($family, $meaning3,
                                                        Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $dimension3->save();
        $this->entityManager->flush();
        $member31 = new Techno_Model_Family_Member($dimension3, $this->keywordService->get($keywordRef3));

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(3, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member11, $member21, $member31]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member31, $member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21, $member31]),
                          $family->getCell([$member31, $member21, $member11]));

        // Delete all
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $meaning1->delete();
        $meaning2->delete();
        $meaning3->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef1));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef2));
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef3));
        $this->entityManager->flush();
    }

    /**
     * Test des cellules avec 2 dimensions et l'utilisation du même keyword pour les 2 dimensions
     *
     * Ce test vérifie qu'il n'y a pas de conflit/mélange dut au fait qu'on utilise le même mot-clé
     * en coordonnées dans les 2 dimensions
     */
    public function testCells2DimensionsSameKeywords()
    {
        $meaning1 = Techno_Test_MeaningTest::generateObject();
        $meaning2 = Techno_Test_MeaningTest::generateObject();
        $keywordRef = strtolower(Core_Tools::generateString(10));
        /** @var KeywordRepository $keywordRepository */
        $keywordRepository = $this->entityManager->getRepository('\Keyword\Domain\Keyword');
        $keywordRepository->add(new Keyword($keywordRef, 'Label'));
        $this->entityManager->flush();

        $family = Techno_Test_Family_CoeffTest::generateObject();

        $dimension1 = new Techno_Model_Family_Dimension($family, $meaning1,
                                                        Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL);
        $dimension1->save();
        $this->entityManager->flush();
        $member11 = new Techno_Model_Family_Member($dimension1, $this->keywordService->get($keywordRef));

        $dimension2 = new Techno_Model_Family_Dimension($family, $meaning2,
                                                        Techno_Model_Family_Dimension::ORIENTATION_VERTICAL);
        $dimension2->save();
        $this->entityManager->flush();
        $member21 = new Techno_Model_Family_Member($dimension2, $this->keywordService->get($keywordRef));

        $family->save();
        $this->entityManager->flush();

        $this->assertCount(2, $family->getDimensions());

        // Vérifie la génération des cellules
        $this->assertAttributeCount(1, 'cells', $family);
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member11, $member21]));
        $this->assertInstanceOf('Techno_Model_Family_Cell', $family->getCell([$member21, $member11]));
        $this->assertSame($family->getCell([$member11, $member21]), $family->getCell([$member21, $member11]));

        // Delete all
        Techno_Test_Family_CoeffTest::deleteObject($family);
        $meaning1->delete();
        $meaning2->delete();
        $keywordRepository->remove($keywordRepository->getByRef($keywordRef));
        $this->entityManager->flush();
    }

}
