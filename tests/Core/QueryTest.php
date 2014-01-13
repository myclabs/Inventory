<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;
use Core_Model_Filter;
use Core_Model_Order;
use Core_Model_Query;
use DateTime;
use Inventory_Model_Simple;
use PHPUnit_Framework_TestSuite;

class QueryTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite(OrderExceptions::class);
        $suite->addTestSuite(FilterExceptions::class);
        $suite->addTestSuite(AclFilterExceptions::class);
        $suite->addTestSuite(QueryExceptions::class);
        $suite->addTestSuite(QueryOthers::class);
        $suite->addTestSuite(LoadListWithQuery::class);
        return $suite;
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Order
 */
class OrderExceptions extends TestCase
{
    /**
     * Vérifie qu'il est impossible de spécifer le tri sur un même attribut deux fois.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testMultipleOrdersOnSameAttribute()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_ASC);
        $query->order->addOrder(Inventory_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);
        $query->order->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe pour désigner la direction.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidOrder()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Simple::QUERY_ID, 'asc');
        $query->order->validate();
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Filter
 */
class FilterExceptions extends TestCase
{
    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe pour spécidifer la condition.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidLogicConnector()
    {
        $query = new Core_Model_Query();
        $query->filter->condition = 'et';
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire d'avoir un tableau de conditions.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidConditions()
    {
        $query = new Core_Model_Query();
        $query->filter->setConditions('conditions');
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est impossible d'avoir une condition sans nom.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testConditionWithNoName()
    {
        $conditionName = null;
        $conditionValue = 'test';
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est impossible d'avoir une condition sans opérateur.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testConditionWithNoOperator()
    {
        $conditionName = 'test';
        $conditionValue = 'test';
        $conditionOperator = null;
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue, $conditionOperator);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est impossible d'avoir une condition sans valeur.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testConditionWithNoValue()
    {
        $conditionName = 'test';
        $conditionValue = null;
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe comme opérateur.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testConditionWithWrongOperator()
    {
        $conditionName = 'test';
        $conditionValue = 'test';
        $conditionOperator = 'et';
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue, $conditionOperator);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire impossible de nommer un SubFilter "main".
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testBadNamingSubFilter()
    {
        $subFilter = 'test';
        $conditionName = 'main';
        $conditionValue = $subFilter;
        $conditionOperator = Core_Model_Filter::OPERATOR_SUB_FILTER;
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue, $conditionOperator);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire d'utiliser un Core_Model_Filter comme SubFulter.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidSubFilter()
    {
        $subFilter = 'test';
        $conditionName = 'test';
        $conditionValue = $subFilter;
        $conditionOperator = Core_Model_Filter::OPERATOR_SUB_FILTER;
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue, $conditionOperator);
        $query->filter->validate();
    }

    /**
     * Vérifie qu'il est nécéssaire qu'un SubFilter possède au moins une condition.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testEmptySubFilter()
    {
        $subFilter = new Core_Model_Filter();
        $conditionName = 'test';
        $conditionValue = $subFilter;
        $conditionOperator = Core_Model_Filter::OPERATOR_SUB_FILTER;
        $query = new Core_Model_Query();
        $query->filter->addCondition($conditionName, $conditionValue, $conditionOperator);
        $query->filter->validate();
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_ACLFilter
 */
class AclFilterExceptions extends TestCase
{
    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe pour spécidifer la condition.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testACLFilterWithoutPrivilege()
    {
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->validate();
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Query
 */
class QueryExceptions extends TestCase
{
    /**
     * Vérifie que startIndex est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidStartIndex()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 'start';
        $query->validate();
    }

    /**
     * Vérifie que startIndex est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testNegativeStartIndex()
    {
        $query = new Core_Model_Query();
        $query->startIndex = -1;
        $query->validate();
    }

    /**
     * Vérifie que totalElements est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidTotalElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = 'total';
        $query->validate();
    }

    /**
     * Vérifie que totalElements doit être un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testNegativeTotalElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = -1;
        $query->validate();
    }

    /**
     * Vérifie que totalElements doit être un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testStartIndexWithoutTotalElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        $query->validate();
    }

    /**
     * Vérifie qu'une exception est lancé si l'on essaye d'accéder à un attribut personnalisé inexistant.
     * @expectedException Core_Exception_UndefinedAttribute
     * @expectedExceptionMessage Attempt to access undefined custom property : undefinedAttribute
     */
    public function testGetUndefinedAttribute()
    {
        $query = new Core_Model_Query();
        $query->undefinedAttribute;
    }

    /**
     * Vérifie qu'une exception est lancé lorsqu'aucun Alias n'est spécifié.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testUndefinedAlias()
    {
        $simpleRepository = $this->entityManager->getRepository(Inventory_Model_Simple::class);
        $queryBuilder = $simpleRepository->createQueryBuilder('test');
        $conditionName = 'test';
        $query = new Core_Model_Query();
        $query->order->addOrder($conditionName);
        $query->parseToQueryBuilderWithoutLimit($queryBuilder);
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Query
 */
class QueryOthers extends TestCase
{
    /**
     * Vérifie le bon fonctionnement du clonage.
     */
    public function testCloning()
    {
        $query = new Core_Model_Query();
        $clonedQuery = clone $query;
        $this->assertNotSame($query, $clonedQuery);
        $this->assertNotSame($query->order, $clonedQuery->order);
        $this->assertNotSame($query->filter, $clonedQuery->filter);
        $this->assertNotSame($query->aclFilter, $clonedQuery->aclFilter);
    }

    /**
     * Vérifie le bon fonctionnement des paramètres personnalisés.
     */
    public function testCustomParameters()
    {
        $query = new Core_Model_Query();
        $query->setCustomParameters(array('test1' => 1));
        $query->test2 = 2;
        $this->assertTrue(isset($query->test1));
        $this->assertTrue(isset($query->test2));
        $this->assertEquals($query->test1, 1);
        $this->assertEquals($query->test2, 2);
        $this->assertEquals($query->getCustomParameters(), array('test1' => 1, 'test2' => 2));
    }
}

/**
 * Test l'execution des LoadList avec des Query.
 */
class LoadListWithQuery extends TestCase
{
    private $simpleEntities = [];

    public function setUp()
    {
        parent::setUp();

        // Création de 5 objets.
        $simpleEntityA1 = new Inventory_Model_Simple();
        $simpleEntityA1->setName('Atest1');
        $simpleEntityA1->setCreationDate(new DateTime('2008-01-01'));
        $this->simpleEntities[] = $simpleEntityA1;
        $simpleEntityB1 = new Inventory_Model_Simple();
        $simpleEntityB1->setName('Btest1');
        $simpleEntityB1->setCreationDate(new DateTime('2009-01-01'));
        $this->simpleEntities[] = $simpleEntityB1;
        $simpleEntityA2 = new Inventory_Model_Simple();
        $simpleEntityA2->setName('Atest2');
        $simpleEntityA2->setCreationDate(new DateTime('2012-01-01'));
        $this->simpleEntities[] = $simpleEntityA2;
        $simpleEntityC1 = new Inventory_Model_Simple();
        $simpleEntityC1->setName('Ctest1');
        $simpleEntityC1->setCreationDate(new DateTime('2010-01-01'));
        $this->simpleEntities[] = $simpleEntityC1;
        $simpleEntityA1b = new Inventory_Model_Simple();
        $simpleEntityA1b->setName('Atest1');
        $simpleEntityA1b->setCreationDate(new DateTime('2011-01-01'));
        $this->simpleEntities[] = $simpleEntityA1b;
        $simpleEntityNull = new Inventory_Model_Simple();
        $this->simpleEntities[] = $simpleEntityNull;

        foreach ($this->simpleEntities as $simpleEntity) {
            $simpleEntity->save();
        }

        $this->entityManager->flush();
    }

    /**
     * Vérifie l'ordre par défault lors d'un loadList.
     */
    public function testDefaultOrder()
    {
        foreach (Inventory_Model_Simple::loadList() as $index => $simpleEntity) {
            $this->assertSame($simpleEntity, $this->simpleEntities[$index]);
        }
    }

    /**
     * Vérifie l'ordre avec un tri sur un seul attribut.
     */
    public function testOrderNameASC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Simple::QUERY_NAME);

        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->simpleEntities[3]);
    }

    /**
     * Vérifie l'ordre avec un tri inverse sur un seul attribut.
     */
    public function testOrderIDDESC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);

        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->simpleEntities[0]);
    }

    /**
     * Vérifie l'ordre avec deux tris.
     */
    public function testOrderNAMEASCIDDESC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Simple::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $query->order->addOrder(Inventory_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);

        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un nombre d'élément maximum.
     */
    public function testListWithMaxElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = 4;
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un offset et un nombre d'élément maximum.
     */
    public function testListWithStartIndexAndLargeMaxElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        $query->totalElements = 10;
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un offset et un nombre d'élément maximum.
     */
    public function testListWithStartIndexAndSmallMaxElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        $query->totalElements = 2;
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(2, count($simpleEntities));
        $this->assertEquals(6, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui contient A.
     */
    public function testFilterCOUNTAINSNametest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Simple::QUERY_NAME, 'test1', Core_Model_Filter::OPERATOR_CONTAINS);
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui commence par A1.
     */
    public function testFilterBEGINSNameA()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Simple::QUERY_NAME, 'A', Core_Model_Filter::OPERATOR_BEGINS);
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui se termine par 2.
     */
    public function testFilterENDSName2()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Simple::QUERY_NAME, '2', Core_Model_Filter::OPERATOR_ENDS);
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[2]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom égal à A.
     */
    public function testFilterEQUALNameA()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Simple::QUERY_NAME, 'A', Core_Model_Filter::OPERATOR_EQUAL);
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(0, count($simpleEntities));
        $this->assertEquals(0, Inventory_Model_Simple::countTotal($query));
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom égal à Btest1.
     */
    public function testFilterEQUALNameBtest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            'Btest1',
            Core_Model_Filter::OPERATOR_EQUAL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[1]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom pas égal à Ctest1.
     */
    public function testFilterNOTEQUALNameCtest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            'Ctest1',
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure à 2009.
     */
    public function testFilterHIGHERDate2009()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2009-01-01'),
            Core_Model_Filter::OPERATOR_HIGHER
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    public function testFilterHIGHEREQUALDate2009()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2009-01-01'),
            Core_Model_Filter::OPERATOR_HIGHER_EQUAL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(5, count($simpleEntities));
        $this->assertEquals(5, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[4], $this->simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    public function testFilterLOWERDate2011()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2011-01-01'),
            Core_Model_Filter::OPERATOR_LOWER
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    public function testFilterLOWEREQUALDate2011()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2011-01-01'),
            Core_Model_Filter::OPERATOR_LOWER_EQUAL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom étant null.
     */
    public function testFilterNULLName()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom n'étant pas null.
     */
    public function testFilterNOTNULLName()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            null,
            Core_Model_Filter::OPERATOR_NOT_NULL
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(5, count($simpleEntities));
        $this->assertEquals(5, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[3]);
        $this->assertSame($simpleEntities[4], $this->simpleEntities[4]);
    }

    /**
     * Vérifie la liste avec plusieurs options de filtre et de tri lié par un connecteur logique AND.
     */
    public function testAdvancedQueryAnd()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2011-01-01'),
            Core_Model_Filter::OPERATOR_LOWER_EQUAL
        );
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            'Ctest1',
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        $query->order->addOrder(
            Inventory_Model_Simple::QUERY_ID,
            Core_Model_Order::ORDER_DESC
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[0]);
    }

    /**
     * Vérifie la liste avec plusieurs options de filtre et de tri lié par un connecteur logique OR.
     */
    public function testAdvancedQueryOR()
    {
        $query = new Core_Model_Query();
        $query->filter->condition = Core_Model_Filter::CONDITION_OR;
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2009-01-01'),
            Core_Model_Filter::OPERATOR_EQUAL
        );
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            'A',
            Core_Model_Filter::OPERATOR_CONTAINS
        );
        $query->order->addOrder(
            Inventory_Model_Simple::QUERY_ID,
            Core_Model_Order::ORDER_DESC
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[0]);
    }

    /**
     * Vérifie la liste avec deux sous-filtres.
     */
    public function testAdvancedSubQuery()
    {
        $subQuery = new Core_Model_Filter();
        $subQuery->addCondition(
            Inventory_Model_Simple::QUERY_DATE,
            new DateTime('2011-01-01'),
            Core_Model_Filter::OPERATOR_LOWER_EQUAL
        );
        $subQuery->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            'Ctest1',
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        $query = new Core_Model_Query();
        $query->filter->condition = Core_Model_Filter::CONDITION_OR;
        $query->filter->addCondition(
            Inventory_Model_Simple::QUERY_NAME,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $query->filter->addCondition(
            'SubQuery',
            $subQuery,
            Core_Model_Filter::OPERATOR_SUB_FILTER
        );
        $query->order->addOrder(
            Inventory_Model_Simple::QUERY_ID,
            Core_Model_Order::ORDER_DESC
        );
        $simpleEntities = Inventory_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Inventory_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->simpleEntities[1]);
        $this->assertSame($simpleEntities[3], $this->simpleEntities[0]);
    }

    protected function tearDown()
    {
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
    }
}
