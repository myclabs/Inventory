<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Core_Exception_InvalidArgument;
use Doctrine\ORM\EntityRepository;
use Inventory_Model_Ordered;
use PHPUnit_Framework_TestSuite;

class EntityOrderedTest
{
    /**
     * Déclaration de la suite de test à éffectuer.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(EntityOrderedCRUD::class);
        $suite->addTestSuite(EntityOrderedOthers::class);
        return $suite;
    }
}

/**
 * Test les fonctionnalités basiques de la strategy Core_Strategy_Ordered.
 */
class EntityOrderedCRUD extends TestCase
{
    /**
     * @var EntityRepository
     */
    protected $orderedEntityRepository;

    public function setUp()
    {
        parent::setUp();
        $this->orderedEntityRepository = $this->entityManager->getRepository('Inventory_Model_Ordered');
    }

    /**
     * Test de la création d'une entité.
     */
    public function testCreateEntity()
    {
        $orderedEntity = new Inventory_Model_Ordered();
        $orderedEntity->save();

        // Vérification que la orderedEntity est gérée par l'EntityManager.
        //  Tant qu'aucun flush n'est fait, l'id reste nulle.
        $this->assertTrue($this->entityManager->contains($orderedEntity));
        $this->assertEmpty($orderedEntity->getKey());
        $this->assertEmpty($this->orderedEntityRepository->findAll());
        $this->assertEquals($orderedEntity->getPosition(), 1);
        // Flush !
        $this->entityManager->flush();
        //  Vérification que l'id est non nulle.
        $this->assertNotEmpty($orderedEntity->getKey());
        $this->assertNotEmpty($this->orderedEntityRepository->findAll());

        return $orderedEntity;
    }

    /**
     * Test du chargement des entités.
     * @depends testCreateEntity
     * @param Inventory_Model_Ordered $orderedEntity
     * @return Inventory_Model_Ordered
     */
    public function testLoadEntity(Inventory_Model_Ordered $orderedEntity)
    {
        // Suppression de la orderedEntity de l'entityManager pour garantir un chargement complet.
        $this->assertTrue($this->entityManager->contains($orderedEntity));
        $this->entityManager->clear();
        $this->assertFalse($this->entityManager->contains($orderedEntity));
        // Chargement d'une nouvelle instance de la orderedEntity.
        $loadedFromBaseOrderedEntity = Inventory_Model_Ordered::load($orderedEntity->getKey());
        // Vérification que l'ancienne orderedEntity à bien été mise de côté au profit de la nouvelle.
        $this->assertFalse($this->entityManager->contains($orderedEntity));
        $this->assertTrue($this->entityManager->contains($loadedFromBaseOrderedEntity));
        $this->assertEquals($loadedFromBaseOrderedEntity, $orderedEntity);
        $this->assertNotSame($loadedFromBaseOrderedEntity, $orderedEntity);
        $this->assertEquals($loadedFromBaseOrderedEntity->getPosition(), 1);
        // Chargement de la même instance de la orderedEntity.
        $loadedFromEntityManagerOrderedEntity = Inventory_Model_Ordered::load($orderedEntity->getKey());
        // Vérification qu'un nouveau chargement utilise la orderedEntity gérée par l'EntityManager.
        $this->assertEquals($loadedFromEntityManagerOrderedEntity, $orderedEntity);
        $this->assertEquals($loadedFromEntityManagerOrderedEntity, $loadedFromBaseOrderedEntity);
        $this->assertNotSame($loadedFromEntityManagerOrderedEntity, $orderedEntity);
        $this->assertSame($loadedFromEntityManagerOrderedEntity, $loadedFromBaseOrderedEntity);
        $this->assertEquals($loadedFromEntityManagerOrderedEntity->getPosition(), 1);
        return $loadedFromEntityManagerOrderedEntity;
    }

    /**
     * Test de la suppression des entités.
     * @depends testLoadEntity
     * @param Inventory_Model_Ordered $orderedEntity
     */
    public function testDeleteEntity(Inventory_Model_Ordered $orderedEntity)
    {
        $orderedEntity->delete();
        // Vérification que l'entity existe toujours bien.
        //  Tant qu'aucun flush n'est fait, l'id existe toujours.
        $this->assertNotEmpty($orderedEntity->getKey());
        $this->assertNotEmpty($this->orderedEntityRepository->findAll());
        $this->assertEquals($orderedEntity->getPosition(), null);
        // Flush !
        $this->entityManager->flush();
        //  Vérification que l'id est nulle.
        $this->assertEmpty($orderedEntity->getKey());
        $this->assertEmpty($this->orderedEntityRepository->findAll());
        $this->assertEquals($orderedEntity->getPosition(), null);
    }

    public static function tearDownAfterClass()
    {
        if (Inventory_Model_Ordered::countTotal() > 0) {
            echo PHP_EOL . 'Des OrderedEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Inventory_Model_Ordered::loadList() as $orderedEntity) {
                $orderedEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}

/**
 * Test les fonctionnalités avancés de Core_Model_Entity.
 */
class EntityOrderedOthers extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (Inventory_Model_Ordered::countTotal() > 0) {
            echo PHP_EOL . 'Des OrderedEntity restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (Inventory_Model_Ordered::loadList() as $orderedEntity) {
                $orderedEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    /**
     * Test la méthode fournissant la plus haute position pour un contexte donnée.
     */
    public function testGetLastPosition()
    {
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 0);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 0);
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 1);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 0);
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 2);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 0);

        $orderedContextedEntity1 = new Inventory_Model_Ordered();
        $orderedContextedEntity1->setContext('context');
        $orderedContextedEntity1->save();
        $orderedContextedEntity2 = new Inventory_Model_Ordered();
        $orderedContextedEntity2->setContext('context');
        $orderedContextedEntity2->save();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 2);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 2);

        $orderedEntity2->delete();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 1);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 2);
        $orderedEntity1->delete();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 0);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 2);

        $orderedContextedEntity1->delete();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 0);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 1);
        $orderedContextedEntity2->delete();
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition(), 0);
        $this->assertEquals(Inventory_Model_Ordered::getLastPosition('context'), 0);
    }

    /**
     * Test la méthode fournissant l'alias d'un classe.
     */
    public function testGoUp()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $orderedEntity3 = new Inventory_Model_Ordered();
        $orderedEntity3->save();
        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 3);
        $orderedEntity2->goUp();
        $this->assertEquals($orderedEntity1->getPosition(), 2);
        $this->assertEquals($orderedEntity2->getPosition(), 1);
        $this->assertEquals($orderedEntity3->getPosition(), 3);
        $orderedEntity3->goUp();
        $this->assertEquals($orderedEntity1->getPosition(), 3);
        $this->assertEquals($orderedEntity2->getPosition(), 1);
        $this->assertEquals($orderedEntity3->getPosition(), 2);
        $orderedEntity3->goUp();
        $this->assertEquals($orderedEntity1->getPosition(), 3);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 1);
        $orderedEntity1->delete();
        $orderedEntity2->delete();
        $orderedEntity3->delete();
    }

    /**
     * Test la méthode fournissant l'alias d'un classe.
     */
    public function testGoDown()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $orderedEntity3 = new Inventory_Model_Ordered();
        $orderedEntity3->save();
        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 3);
        $orderedEntity2->goDown();
        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 2);
        $orderedEntity1->goDown();
        $this->assertEquals($orderedEntity1->getPosition(), 2);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 1);
        $orderedEntity1->goDown();
        $this->assertEquals($orderedEntity1->getPosition(), 3);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 1);
        $orderedEntity1->delete();
        $orderedEntity2->delete();
        $orderedEntity3->delete();
    }

    /**
     * Test la méthode fournissant l'alias d'un classe.
     */
    public function testSetPosition()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $orderedEntity3 = new Inventory_Model_Ordered();
        $orderedEntity3->save();
        $orderedEntity4 = new Inventory_Model_Ordered();
        $orderedEntity4->save();
        $orderedEntity5 = new Inventory_Model_Ordered();
        $orderedEntity5->save();

        $orderedContextedEntity1 = new Inventory_Model_Ordered();
        $orderedContextedEntity1->setContext('context');
        $orderedContextedEntity1->save();
        $orderedContextedEntity2 = new Inventory_Model_Ordered();
        $orderedContextedEntity2->setContext('context');
        $orderedContextedEntity2->save();
        $orderedContextedEntity3 = new Inventory_Model_Ordered();
        $orderedContextedEntity3->setContext('context');
        $orderedContextedEntity3->save();

        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 3);
        $this->assertEquals($orderedEntity4->getPosition(), 4);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity4->setPosition(2);
        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 4);
        $this->assertEquals($orderedEntity4->getPosition(), 2);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity1->setPosition(3);
        $this->assertEquals($orderedEntity1->getPosition(), 3);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 4);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity5->setPosition(2);
        $this->assertEquals($orderedEntity1->getPosition(), 4);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 5);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedContextedEntity3->setPosition(1);
        $this->assertEquals($orderedEntity1->getPosition(), 4);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 5);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 3);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 1);

        $orderedEntity1->delete();
        $orderedEntity2->delete();
        $orderedEntity3->delete();
        $orderedEntity4->delete();
        $orderedEntity5->delete();
        $orderedContextedEntity1->delete();
        $orderedContextedEntity2->delete();
        $orderedContextedEntity3->delete();
    }

    /**
     * Déplacement après une autre entité
     */
    public function testMoveAfter()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $orderedEntity3 = new Inventory_Model_Ordered();
        $orderedEntity3->save();
        $orderedEntity4 = new Inventory_Model_Ordered();
        $orderedEntity4->save();
        $orderedEntity5 = new Inventory_Model_Ordered();
        $orderedEntity5->save();

        $orderedContextedEntity1 = new Inventory_Model_Ordered();
        $orderedContextedEntity1->setContext('context');
        $orderedContextedEntity1->save();
        $orderedContextedEntity2 = new Inventory_Model_Ordered();
        $orderedContextedEntity2->setContext('context');
        $orderedContextedEntity2->save();
        $orderedContextedEntity3 = new Inventory_Model_Ordered();
        $orderedContextedEntity3->setContext('context');
        $orderedContextedEntity3->save();

        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 3);
        $this->assertEquals($orderedEntity4->getPosition(), 4);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity4->moveAfter($orderedEntity1);
        $this->assertEquals($orderedEntity1->getPosition(), 1);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 4);
        $this->assertEquals($orderedEntity4->getPosition(), 2);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity1->moveAfter($orderedEntity2);
        $this->assertEquals($orderedEntity1->getPosition(), 3);
        $this->assertEquals($orderedEntity2->getPosition(), 2);
        $this->assertEquals($orderedEntity3->getPosition(), 4);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 5);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedEntity5->moveAfter($orderedEntity4);
        $this->assertEquals($orderedEntity1->getPosition(), 4);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 5);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 3);

        $orderedContextedEntity3->moveAfter($orderedContextedEntity1);
        $this->assertEquals($orderedEntity1->getPosition(), 4);
        $this->assertEquals($orderedEntity2->getPosition(), 3);
        $this->assertEquals($orderedEntity3->getPosition(), 5);
        $this->assertEquals($orderedEntity4->getPosition(), 1);
        $this->assertEquals($orderedEntity5->getPosition(), 2);
        $this->assertEquals($orderedContextedEntity1->getPosition(), 1);
        $this->assertEquals($orderedContextedEntity2->getPosition(), 3);
        $this->assertEquals($orderedContextedEntity3->getPosition(), 2);

        $orderedEntity1->delete();
        $orderedEntity2->delete();
        $orderedEntity3->delete();
        $orderedEntity4->delete();
        $orderedEntity5->delete();
        $orderedContextedEntity1->delete();
        $orderedContextedEntity2->delete();
        $orderedContextedEntity3->delete();
    }

    /**
     * Déplacement après une autre entité
     * @expectedException \Core_Exception_InvalidArgument
     */
    public function testMoveAfterDifferentContexts()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->setContext('context1');
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->setContext('context2');
        $orderedEntity2->save();

        try {
            $orderedEntity1->moveAfter($orderedEntity2);
        } catch (Core_Exception_InvalidArgument $e) {
            $orderedEntity1->delete();
            $orderedEntity2 ->delete();
            throw $e;
        }
    }

    /**
     * Test la méthode loadByPosition
     */
    public function testLoadByPosition()
    {
        $orderedEntity1 = new Inventory_Model_Ordered();
        $orderedEntity1->save();
        $orderedEntity2 = new Inventory_Model_Ordered();
        $orderedEntity2->save();
        $orderedEntity3 = new Inventory_Model_Ordered();
        $orderedEntity3->save();
        $orderedContextedEntity1 = new Inventory_Model_Ordered();
        $orderedContextedEntity1->setContext('context');
        $orderedContextedEntity1->save();
        $orderedContextedEntity2 = new Inventory_Model_Ordered();
        $orderedContextedEntity2->setContext('context');
        $orderedContextedEntity2->save();
        $this->entityManager->flush();

        $loadedOrderedEntity2 = Inventory_Model_Ordered::loadByPosition(2);
        $this->assertSame($loadedOrderedEntity2, $orderedEntity2);

        $loadedOrderedContextedEntity2 = Inventory_Model_Ordered::loadByPosition(2, 'context');
        $this->assertSame($loadedOrderedEntity2, $orderedEntity2);

        $orderedEntity1->delete();
        $orderedEntity2->delete();
        $orderedEntity3->delete();
        $orderedContextedEntity1->delete();
        $orderedContextedEntity2->delete();
        $this->entityManager->flush();
    }

    public static function tearDownAfterClass()
    {
        if (Inventory_Model_Ordered::countTotal() > 0) {
            echo PHP_EOL . 'Des OrderedEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Inventory_Model_Ordered::loadList() as $orderedEntity) {
                $orderedEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
