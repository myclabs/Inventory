<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Core_Model_Filter;
use Core_Model_Order;
use Core_Model_Query;
use DateTime;
use Inventory_Model_Association;
use Inventory_Model_Simple;

class EntityAssociationTest extends TestCase
{
    /**
     * @var Inventory_Model_Simple[]
     */
    protected $simpleEntities = [];

    /**
     * @var Inventory_Model_Association[]
     */
    protected $associationEntities = [];

    public static function setUpBeforeClass()
    {
        if (Inventory_Model_Association::countTotal() > 0) {
            echo PHP_EOL . 'Des AssociationEntity restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (Inventory_Model_Association::loadList() as $associationEntity) {
                $associationEntity->delete();
            }
            self::getEntityManager()->flush();
        }
        if (Inventory_Model_Simple::countTotal() > 0) {
            echo PHP_EOL . 'Des SimpleEntity restantes ont été trouvé avant les tests, suppression en cours !';
            foreach (Inventory_Model_Simple::loadList() as $simpleEntity) {
                $simpleEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function setUp()
    {
        parent::setUp();

        // Création de 11 objets Simple.
        $simpleEntityNull = new Inventory_Model_Simple();
        $this->simpleEntities[] = $simpleEntityNull;
        $simpleEntityA1 = new Inventory_Model_Simple();
        $simpleEntityA1->setName('Asimple1');
        $simpleEntityA1->setCreationDate(new DateTime('2008-01-01'));
        $this->simpleEntities[] = $simpleEntityA1;
        $simpleEntityB1 = new Inventory_Model_Simple();
        $simpleEntityB1->setName('Bsimple1');
        $simpleEntityB1->setCreationDate(new DateTime('2009-01-01'));
        $this->simpleEntities[] = $simpleEntityB1;
        $simpleEntityA2 = new Inventory_Model_Simple();
        $simpleEntityA2->setName('Asimple2');
        $simpleEntityA2->setCreationDate(new DateTime('2012-01-01'));
        $this->simpleEntities[] = $simpleEntityA2;
        $simpleEntityB2 = new Inventory_Model_Simple();
        $simpleEntityB2->setName('Bsimple2');
        $simpleEntityB2->setCreationDate(new DateTime('2012-01-01'));
        $this->simpleEntities[] = $simpleEntityB2;
        $simpleEntityC1 = new Inventory_Model_Simple();
        $simpleEntityC1->setName('Csimple1');
        $simpleEntityC1->setCreationDate(new DateTime('2010-01-01'));
        $this->simpleEntities[] = $simpleEntityC1;
        $simpleEntityC2 = new Inventory_Model_Simple();
        $simpleEntityC2->setName('Csimple2');
        $simpleEntityC2->setCreationDate(new DateTime('2010-01-01'));
        $this->simpleEntities[] = $simpleEntityC2;
        $simpleEntityA1b = new Inventory_Model_Simple();
        $simpleEntityA1b->setName('Asimple1');
        $simpleEntityA1b->setCreationDate(new DateTime('2011-01-01'));
        $this->simpleEntities[] = $simpleEntityA1b;
        $simpleEntityD1 = new Inventory_Model_Simple();
        $simpleEntityD1->setName('Dsimple1');
        $simpleEntityD1->setCreationDate(new DateTime('2013-01-01'));
        $this->simpleEntities[] = $simpleEntityD1;
        $simpleEntityDateNull = new Inventory_Model_Simple();
        $simpleEntityDateNull->setName('EsimpleDateNull');
        $this->simpleEntities[] = $simpleEntityDateNull;
        $simpleEntityNameNull = new Inventory_Model_Simple();
        $simpleEntityNameNull->setCreationDate(new DateTime('2007-01-01'));
        $this->simpleEntities[] = $simpleEntityNameNull;

        foreach ($this->simpleEntities as $simpleEntity) {
            $simpleEntity->save();
        }

        // Création de 5 objets Association.
        $associationEntityA1 = new Inventory_Model_Association();
        $associationEntityA1->setName('Aassociation1');
        $associationEntityA1->addSimple($simpleEntityA1);
        $associationEntityA1->addSimple($simpleEntityA1b);
        $associationEntityA1->addSimple($simpleEntityC1);
        $associationEntityA1->addSimple($simpleEntityD1);
        $this->associationEntities[] = $associationEntityA1;
        $associationEntityA2 = new Inventory_Model_Association();
        $associationEntityA2->setName('Bassociation1');
        $associationEntityA2->addSimple($simpleEntityA2);
        $associationEntityA2->addSimple($simpleEntityB2);
        $associationEntityA2->addSimple($simpleEntityC2);
        $this->associationEntities[] = $associationEntityA2;
        $associationEntityB1 = new Inventory_Model_Association();
        $associationEntityB1->setName('Aassociation2');
        $associationEntityB1->addSimple($simpleEntityA1);
        $associationEntityB1->addSimple($simpleEntityA1b);
        $associationEntityB1->addSimple($simpleEntityC1);
        $associationEntityB1->addSimple($simpleEntityD1);
        $this->associationEntities[] = $associationEntityB1;
        $associationEntityB2 = new Inventory_Model_Association();
        $associationEntityB2->setName('Bassociation2');
        $associationEntityB2->addSimple($simpleEntityD1);
        $this->associationEntities[] = $associationEntityB2;
        $associationEntityNull = new Inventory_Model_Association();
        $this->associationEntities[] = $associationEntityNull;

        foreach ($this->associationEntities as $associationEntity) {
            $associationEntity->save();
        }

        $this->entityManager->flush();
    }

    /**
     * Test le loadList par défaut.
     */
    public function testBasicLoadList()
    {
        $associationEntities = Inventory_Model_Association::loadList();
        $this->assertEquals(5, count($associationEntities));
        $this->assertEquals(5, Inventory_Model_Association::countTotal());
        $this->assertSame($associationEntities[0], $this->associationEntities[0]);
        $this->assertSame($associationEntities[1], $this->associationEntities[1]);
        $this->assertSame($associationEntities[2], $this->associationEntities[2]);
        $this->assertSame($associationEntities[3], $this->associationEntities[3]);
        $this->assertSame($associationEntities[4], $this->associationEntities[4]);
    }

    /**
     * Test le bon fonction d'une méthode Repository personnalisé.
     */
    public function testCustomRepositoryFunction()
    {
        $query = new Core_Model_Query();
        $query->xSimple = 2;
        $associationEntities = Inventory_Model_Association::loadWithMoreThanXSimple($query);
        $this->assertEquals(3, count($associationEntities));
        $this->assertEquals(3, Inventory_Model_Association::countWithMoreThanXSimple($query));
        $this->assertSame($associationEntities[0], $this->associationEntities[0]);
        $this->assertSame($associationEntities[1], $this->associationEntities[1]);
        $this->assertSame($associationEntities[2], $this->associationEntities[2]);
    }

    /**
     * Test le bon fonction d'une méthode Repository personnalisé avec une Query avancée.
     */
    public function testCustomRepositoryFunctionWithFilterAndOrder()
    {
        $query = new Core_Model_Query();
        $query->xSimple = 2;
        $query->filter->addCondition(Inventory_Model_Simple::QUERY_NAME, '1',
                Core_Model_Filter::OPERATOR_CONTAINS, Inventory_Model_Simple::getAlias());
        $query->order->addOrder(Inventory_Model_Association::QUERY_NAME, Core_Model_Order::ORDER_DESC);
        $associationEntities = Inventory_Model_Association::loadWithMoreThanXSimple($query);
        $this->assertEquals(2, count($associationEntities));
        $this->assertEquals(2, Inventory_Model_Association::countWithMoreThanXSimple($query));
        $this->assertSame($associationEntities[0], $this->associationEntities[2]);
        $this->assertSame($associationEntities[1], $this->associationEntities[0]);
    }

    protected function tearDown()
    {
        foreach ($this->associationEntities as $associationEntity) {
            $associationEntity->delete();
        }
        $this->entityManager->flush();
        foreach ($this->simpleEntities as $simpleEntity) {
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
        if (Inventory_Model_Association::countTotal() > 0) {
            echo PHP_EOL . 'Des AssociationEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Inventory_Model_Association::loadList() as $associationEntity) {
                $associationEntity->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
