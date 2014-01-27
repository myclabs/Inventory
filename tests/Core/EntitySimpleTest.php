<?php

namespace Tests\Core;

use Core\Domain\EntityRepository;
use Core\Test\TestCase;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Inventory_Model_Simple;
use PHPUnit_Framework_TestSuite;

class EntitySimpleTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(EntitySimpleCRUD::class);
        $suite->addTestSuite(EntitySimpleOthers::class);
        return $suite;
    }
}

/**
 * Test les fonctionnalités basiques de Core_Model_Entity.
 */
class EntitySimpleCRUD extends TestCase
{
    /**
     * @var EntityRepository
     */
    protected $simpleEntityRepository;

    public function setUp()
    {
        parent::setUp();
        $this->simpleEntityRepository = $this->entityManager->getRepository(Inventory_Model_Simple::class);
    }

    /**
     * Test de la création d'une entité.
     */
    public function testCreateEntity()
    {
        $simpleEntity = new Inventory_Model_Simple();
        $simpleEntity->save();

        $this->assertInstanceOf('Inventory_Model_Simple', $simpleEntity);
        // Vérification que la simpleEntity est gérée par l'EntityManager.
        //  Tant qu'aucun flush n'est fait, l'id reste nulle.
        $this->assertTrue($this->entityManager->contains($simpleEntity));
        $this->assertEmpty($simpleEntity->getKey());
        $this->assertEmpty($this->simpleEntityRepository->findAll());
        // Flush !
        $this->entityManager->flush();
        //  Vérification que l'id est non nulle.
        $this->assertNotEmpty($simpleEntity->getKey());
        $this->assertNotEmpty($this->simpleEntityRepository->findAll());

        return $simpleEntity;
    }

    /**
     * Test du chargement des entités.
     * @depends testCreateEntity
     * @param Inventory_Model_Simple $simpleEntity
     * @return Inventory_Model_Simple
     */
    public function testLoadEntity(Inventory_Model_Simple $simpleEntity)
    {
        // Suppression de la simpleEntity de l'entityManager pour garantir un chargement complet.
        $this->assertTrue($this->entityManager->contains($simpleEntity));
        $this->entityManager->clear();
        $this->assertFalse($this->entityManager->contains($simpleEntity));
        // Chargement d'une nouvelle instance de la simpleEntity.
        $loadedFromBaseSimpleEntity = Inventory_Model_Simple::load($simpleEntity->getKey());
        // Vérification que l'ancienne simpleEntity à bien été mise de côté au profit de la nouvelle.
        $this->assertFalse($this->entityManager->contains($simpleEntity));
        $this->assertTrue($this->entityManager->contains($loadedFromBaseSimpleEntity));
        $this->assertEquals($loadedFromBaseSimpleEntity, $simpleEntity);
        $this->assertNotSame($loadedFromBaseSimpleEntity, $simpleEntity);
        // Chargement de la même instance de la simpleEntity.
        $loadedFromEntityManagerSimpleEntity = Inventory_Model_Simple::load($simpleEntity->getKey());
        // Vérification qu'un nouveau chargement utilise la simpleEntity gérée par l'EntityManager.
        $this->assertEquals($loadedFromEntityManagerSimpleEntity, $simpleEntity);
        $this->assertEquals($loadedFromEntityManagerSimpleEntity, $loadedFromBaseSimpleEntity);
        $this->assertNotSame($loadedFromEntityManagerSimpleEntity, $simpleEntity);
        $this->assertSame($loadedFromEntityManagerSimpleEntity, $loadedFromBaseSimpleEntity);
        return $loadedFromEntityManagerSimpleEntity;
    }

    /**
     * Test de la suppression des entités.
     * @depends testLoadEntity
     * @param Inventory_Model_Simple $simpleEntity
     */
    public function testDeleteEntity(Inventory_Model_Simple $simpleEntity)
    {
        $simpleEntity->delete();
        // Vérification que l'entity existe toujours bien.
        //  Tant qu'aucun flush n'est fait, l'id existe toujours.
        $this->assertNotEmpty($simpleEntity->getKey());
        $this->assertNotEmpty($this->simpleEntityRepository->findAll());
        // Flush !
        $this->entityManager->flush();
        //  Vérification que l'id est nulle.
        $this->assertEmpty($simpleEntity->getKey());
        $this->assertEmpty($this->simpleEntityRepository->findAll());
    }

    public static function tearDownAfterClass()
    {
        if (Inventory_Model_Simple::countTotal() > 0) {
            echo PHP_EOL . 'Des SimpleEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Inventory_Model_Simple::loadList() as $simpleEntity) {
                $simpleEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}

/**
 * Test les fonctionnalités avancés de Core_Model_Entity.
 */
class EntitySimpleOthers extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (Inventory_Model_Simple::countTotal() > 0) {
            echo PHP_EOL . 'Des SimpleEntity restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (Inventory_Model_Simple::loadList() as $simpleEntity) {
                $simpleEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    /**
     * Test la méthode fournissant l'alias d'un classe.
     */
    public function testAlias()
    {
        $simpleEntity = new Inventory_Model_Simple();
        $this->assertEquals($simpleEntity::getAlias(), 'i_s');
        $simpleEntity->delete();
    }

    /**
     * Test que l'EntityManager actif que la classe est bien celui par défault.
     */
    public function testGetPoolName()
    {
        $this->assertEquals(Inventory_Model_Simple::getActivePoolName(), 'default');
    }

    /**
     * Test l'exception sur le chargement non trouvé.
     * @expectedException Core_Exception_NotFound
     */
    public function testLoadNotFound()
    {
        $id = array('id' => 42);
        Inventory_Model_Simple::load($id);
    }

    /**
     * Test l'exception sur le chargement par attribut avec trop de réponses.
     * @expectedException Core_Exception_NotFound
     */
    public function testLoadByNotFound()
    {
        Inventory_Model_Simple::loadByName('A');
    }

    /**
     * Test l'exception sur le chargement par attribut avec trop de réponses.
     * @expectedException Core_Exception_TooMany
     */
    public function testLoadByTooMany()
    {
        // Construction de deux SimpleEntity nommé A.
        $simpleEntityA = new Inventory_Model_Simple();
        $simpleEntityA->setName('A');
        $simpleEntityA->save();
        $simpleEntityABis = new Inventory_Model_Simple();
        $simpleEntityABis->setName('A');
        $simpleEntityABis->save();

        // Enregistrement des SimpleEntity en base.
        $this->entityManager->flush();

        try {
            Inventory_Model_Simple::loadByName('A');
        } catch (Core_Exception_TooMany $e) {
            $simpleEntityA->delete();
            $simpleEntityABis->delete();
            $this->entityManager->flush();
            throw $e;
        }
        $simpleEntityA->delete();
        $simpleEntityABis->delete();
        $this->entityManager->flush();
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Test le chargement en fonction d'un attribut de l'objet.
     */
    public function testLoadBy()
    {
        // Construction de deux SimpleEntity nommé A.
        $simpleEntityB = new Inventory_Model_Simple();
        $simpleEntityB->setName('B');
        $simpleEntityB->save();

        // Enregistrement des SimpleEntity en base.
        $this->entityManager->flush();

        $simpleLoadedEntityB = Inventory_Model_Simple::loadByName('B');

        $this->assertSame($simpleLoadedEntityB, $simpleEntityB);

        $simpleEntityB->delete();
        $this->entityManager->flush();
    }

    /**
     * Test le chargement d'une liste d'entités.
     */
    public function testLoadListAndCountTotalEntity()
    {
        // Construction de 3 SimpleEntity aux noms différents.
        $simpleEntity1 = new Inventory_Model_Simple();
        $simpleEntity1->setName('1');
        $simpleEntity1->save();
        $simpleEntity2 = new Inventory_Model_Simple();
        $simpleEntity2->setName('2');
        $simpleEntity2->save();
        $simpleEntity3 = new Inventory_Model_Simple();
        $simpleEntity3->setName('3');
        $simpleEntity3->save();

        // Le loadList ne renvoie aucune des SimpleEntity tant qu'un flush n'a pas été fait.
        $this->assertEmpty(Inventory_Model_Simple::loadList());

        // Enregistrement des SimpleEntity en base.
        $this->entityManager->flush();

        // Chargement des SimpleEntity via le loadList.
        $listSimpleEntities = Inventory_Model_Simple::loadList();
        $countSimpleEntities = Inventory_Model_Simple::countTotal();
        $listRetrievedNames = array();
        foreach ($listSimpleEntities as $simpleEntity) {
            $this->assertInstanceOf('Inventory_Model_Simple', $simpleEntity);
            $this->assertTrue(in_array($simpleEntity->getName(), array('1', '2', '3')));
            $listRetrievedNames[$simpleEntity->getName()] = true;
        }
        // Vérification que l'on a bien récupéré 3 SimpleEntity et les 3 noms différents.
        $this->assertEquals(count($listRetrievedNames), 3);
        $this->assertEquals(count($listRetrievedNames), count($listSimpleEntities));
        $this->assertEquals(count($listRetrievedNames), $countSimpleEntities);

        // Suppression des SimpleEntity.
        foreach ($listSimpleEntities as $simpleEntity) {
            $simpleEntity->delete();
        }
        $this->entityManager->flush();
    }

    public static function tearDownAfterClass()
    {
        if (Inventory_Model_Simple::countTotal() > 0) {
            echo PHP_EOL . 'Des SimpleEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Inventory_Model_Simple::loadList() as $simpleEntity) {
                $simpleEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
