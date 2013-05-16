<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Test
 */

/**
 * Test l'utilisation des filtres et des tris.
 * @package    Core
 * @subpackage Test
 */
class Core_Test_QueryTest
{
    /**
     * Déclaration de la suite de test à effectuer.
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Core_Test_OrderExceptions');
        $suite->addTestSuite('Core_Test_FilterExceptions');
        $suite->addTestSuite('Core_Test_AclFilterExceptions');
        $suite->addTestSuite('Core_Test_QueryExceptions');
        $suite->addTestSuite('Core_Test_QueryOthers');
        $suite->addTestSuite('Core_Test_LoadListWithQuety');
        return $suite;
    }
}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Order
 * @package    Core
 * @subpackage Test
 */
class Core_Test_OrderExceptions extends PHPUnit_Framework_TestCase
{

    /**
     * Vérifie qu'il est impossible de spécifer le tri sur un même attribut deux fois.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testMultipleOrdersOnSameAttribute()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_ASC);
        $query->order->addOrder(Default_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);
        try {
            $query->order->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Order for '.Default_Model_Simple::QUERY_ID.'" has already been specified.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe pour désigner la direction.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidOrder()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Simple::QUERY_ID, 'asc');
        try {
            $query->order->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Sort direction for "'.Default_Model_Simple::QUERY_ID.'" is invald.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Filter
 * @package    Core
 * @subpackage Test
 */
class Core_Test_FilterExceptions extends PHPUnit_Framework_TestCase
{

    /**
     * Vérifie qu'il est nécéssaire d'utiliser les constantes de la classe pour spécidifer la condition.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidLogicConnector()
    {
        $query = new Core_Model_Query();
        $query->filter->condition = 'et';
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'The logical connector has to be a class constant : "CONDITION".') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie qu'il est nécéssaire d'avoir un tableau de conditions.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidConditions()
    {
        $query = new Core_Model_Query();
        $query->filter->setConditions('conditions');
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Invalid data format for attribute "_conditions".') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'One of the conditions has no name.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Condition "'.$conditionName.'" has no operator.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Condition "'.$conditionName.'" has no value.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'Condition "'.$conditionName.'" has an invalid operator.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'SubFilter name "'.$conditionName.'" is the main Filter.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'SubFilter "'.$conditionName.'" must be a Filter.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
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
        try {
            $query->filter->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'SubFilter "'.$conditionName.'" must have one condition.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_AclFilter
 * @package    Core
 * @subpackage Test
 */
class Core_Test_AclFilterExceptions extends PHPUnit_Framework_TestCase
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
 * @package    Core
 * @subpackage Test
 */
class Core_Test_QueryExceptions extends PHPUnit_Framework_TestCase
{

    /**
     * Vérifie que startIndex est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidStartIndex()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 'start';
        try {
            $query->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'startIndex has invalid value (should be 0 or positive int)') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie que startIndex est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testNegativeStartIndex()
    {
        $query = new Core_Model_Query();
        $query->startIndex = -1;
        try {
            $query->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'startIndex has invalid value (should be 0 or positive int)') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie que totalElements est un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testInvalidTotalElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = 'total';
        try {
            $query->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'totalElements has invalid value (should be a positive int)') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie que totalElements doit être un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testNegativeTotalElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = -1;
        try {
            $query->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'totalElements has invalid value (should be a positive int)') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie que totalElements doit être un entier positif.
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testStartIndexWithoutTotalElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        try {
            $query->validate();
        } catch (Core_Exception_InvalidArgument $e) {
            if ($e->getMessage() == 'When totalElements is null, startIndex has to be null too.') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie qu'une exception est lancé si l'on essaye d'accéder à un attribut personnalisé inexistant.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testGetUndefinedAttribute()
    {
        $query = new Core_Model_Query();
        try {
            $valueUndefinedAttribute = $query->undefinedAttribute;
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() == 'Attempt to access undefined custom attribute : undefinedAttribute') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Vérifie qu'une exception est lancé lorsqu'aucun Alias n'est spécifié.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    public function testUndefinedAlias()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $simpleRepository = $entityManagers['default']->getRepository('Default_Model_Simple');
        $queryBuilder = $simpleRepository->createQueryBuilder('test');
        $conditionName = 'test';
        $query = new Core_Model_Query();
        $query->order->addOrder($conditionName);
        try {
            $query->getQueryWithoutLimit($queryBuilder);
        } catch (Core_Exception_UndefinedAttribute $e) {
            if ($e->getMessage() == 'Neither Alias or RootAlias for condition "'.$conditionName.'" are defined') {
                throw $e;
            }
        }
        $this->fail('An expected exception has not been raised.');
    }

}

/**
 * Vérifie les exceptions lancées par la classe Core_Model_Query
 * @package    Core
 * @subpackage Test
 */
class Core_Test_QueryOthers extends PHPUnit_Framework_TestCase
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
 * @package    Core
 * @subpackage Test
 */
class Core_Test_LoadListWithQuety extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests.
     */
    public function setUp()
    {
        // Création de 5 objets.
        $simpleEntityA1 = new Default_Model_Simple();
        $simpleEntityA1->setName('Atest1');
        $simpleEntityA1->setCreationDate(new DateTime('2008-01-01'));
        $this->_simpleEntities[] = $simpleEntityA1;
        $simpleEntityB1 = new Default_Model_Simple();
        $simpleEntityB1->setName('Btest1');
        $simpleEntityB1->setCreationDate(new DateTime('2009-01-01'));
        $this->_simpleEntities[] = $simpleEntityB1;
        $simpleEntityA2 = new Default_Model_Simple();
        $simpleEntityA2->setName('Atest2');
        $simpleEntityA2->setCreationDate(new DateTime('2012-01-01'));
        $this->_simpleEntities[] = $simpleEntityA2;
        $simpleEntityC1 = new Default_Model_Simple();
        $simpleEntityC1->setName('Ctest1');
        $simpleEntityC1->setCreationDate(new DateTime('2010-01-01'));
        $this->_simpleEntities[] = $simpleEntityC1;
        $simpleEntityA1b = new Default_Model_Simple();
        $simpleEntityA1b->setName('Atest1');
        $simpleEntityA1b->setCreationDate(new DateTime('2011-01-01'));
        $this->_simpleEntities[] = $simpleEntityA1b;
        $simpleEntityNull = new Default_Model_Simple();
        $this->_simpleEntities[] = $simpleEntityNull;

        foreach ($this->_simpleEntities as $simpleEntity) {
            $simpleEntity->save();
        }

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Vérifie l'ordre par défault lors d'un loadList.
     */
    function testDefaultOrder()
    {
        foreach (Default_Model_Simple::loadList() as $index => $simpleEntity) {
            $this->assertSame($simpleEntity, $this->_simpleEntities[$index]);
        }
    }

    /**
     * Vérifie l'ordre avec un tri sur un seul attribut.
     */
    function testOrderNameASC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Simple::QUERY_NAME);

        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->_simpleEntities[3]);
    }

    /**
     * Vérifie l'ordre avec un tri inverse sur un seul attribut.
     */
    function testOrderIDDESC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);

        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->_simpleEntities[0]);
    }

    /**
     * Vérifie l'ordre avec deux tris.
     */
    function testOrderNAMEASCIDDESC()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Simple::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $query->order->addOrder(Default_Model_Simple::QUERY_ID, Core_Model_Order::ORDER_DESC);

        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(6, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[4], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[5], $this->_simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un nombre d'élément maximum.
     */
    function testListWithMaxElements()
    {
        $query = new Core_Model_Query();
        $query->totalElements = 4;
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un offset et un nombre d'élément maximum.
     */
    function testListWithStartIndexAndLargeMaxElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        $query->totalElements = 10;
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un offset et un nombre d'élément maximum.
     */
    function testListWithStartIndexAndSmallMaxElements()
    {
        $query = new Core_Model_Query();
        $query->startIndex = 2;
        $query->totalElements = 2;
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(2, count($simpleEntities));
        $this->assertEquals(6, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui contient A.
     */
    function testFilterCOUNTAINSNametest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'test1', Core_Model_Filter::OPERATOR_CONTAINS);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui commence par A1.
     */
    function testFilterBEGINSNameA()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'A', Core_Model_Filter::OPERATOR_BEGINS);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom qui se termine par 2.
     */
    function testFilterENDSName2()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, '2', Core_Model_Filter::OPERATOR_ENDS);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[2]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom égal à A.
     */
    function testFilterEQUALNameA()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'A', Core_Model_Filter::OPERATOR_EQUAL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(0, count($simpleEntities));
        $this->assertEquals(0, Default_Model_Simple::countTotal($query));
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom égal à Btest1.
     */
    function testFilterEQUALNameBtest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'Btest1',
                Core_Model_Filter::OPERATOR_EQUAL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[1]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom pas égal à Ctest1.
     */
    function testFilterNOTEQUALNameCtest1()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'Ctest1',
                Core_Model_Filter::OPERATOR_NOT_EQUAL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure à 2009.
     */
    function testFilterHIGHERDate2009()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2009-01-01'),
                Core_Model_Filter::OPERATOR_HIGHER);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    function testFilterHIGHEREQUALDate2009()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2009-01-01'),
                Core_Model_Filter::OPERATOR_HIGHER_EQUAL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(5, count($simpleEntities));
        $this->assertEquals(5, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[4], $this->_simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    function testFilterLOWERDate2011()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2011-01-01'),
                Core_Model_Filter::OPERATOR_LOWER);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[3]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur la date supérieure ou égale à 2009.
     */
    function testFilterLOWEREQUALDate2011()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2011-01-01'),
                Core_Model_Filter::OPERATOR_LOWER_EQUAL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[4]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom étant null.
     */
    function testFilterNULLName()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, null,
                Core_Model_Filter::OPERATOR_NULL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(1, count($simpleEntities));
        $this->assertEquals(1, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[5]);
    }

    /**
     * Vérifie la liste d'éléments avec un filtre sur le nom n'étant pas null.
     */
    function testFilterNOTNULLName()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, null,
                Core_Model_Filter::OPERATOR_NOT_NULL);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(5, count($simpleEntities));
        $this->assertEquals(5, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[0]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[3]);
        $this->assertSame($simpleEntities[4], $this->_simpleEntities[4]);
    }

    /**
     * Vérifie la liste avec plusieurs options de filtre et de tri lié par un connecteur logique AND.
     */
    function testAdvancedQueryAnd()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2011-01-01'),
                Core_Model_Filter::OPERATOR_LOWER_EQUAL);
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'Ctest1',
                Core_Model_Filter::OPERATOR_NOT_EQUAL);
        $query->order->addOrder(Default_Model_Simple::QUERY_ID,
                Core_Model_Order::ORDER_DESC);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(3, count($simpleEntities));
        $this->assertEquals(3, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[0]);
    }

    /**
     * Vérifie la liste avec plusieurs options de filtre et de tri lié par un connecteur logique OR.
     */
    function testAdvancedQueryOR()
    {
        $query = new Core_Model_Query();
        $query->filter->condition = Core_Model_Filter::CONDITION_OR;
        $query->filter->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2009-01-01'),
                Core_Model_Filter::OPERATOR_EQUAL);
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, 'A',
                Core_Model_Filter::OPERATOR_CONTAINS);
        $query->order->addOrder(Default_Model_Simple::QUERY_ID,
                Core_Model_Order::ORDER_DESC);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[2]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[0]);
    }

    /**
     * Vérifie la liste avec deux sous-filtres.
     */
    function testAdvancedSubQuery()
    {
        $subQuery = new Core_Model_Filter();
        $subQuery->addCondition(Default_Model_Simple::QUERY_DATE, new DateTime('2011-01-01'),
                Core_Model_Filter::OPERATOR_LOWER_EQUAL);
        $subQuery->addCondition(Default_Model_Simple::QUERY_NAME, 'Ctest1',
                Core_Model_Filter::OPERATOR_NOT_EQUAL);
        $query = new Core_Model_Query();
        $query->filter->condition = Core_Model_Filter::CONDITION_OR;
        $query->filter->addCondition(Default_Model_Simple::QUERY_NAME, null,
                Core_Model_Filter::OPERATOR_NULL);
        $query->filter->addCondition('SubQuery', $subQuery,
                Core_Model_Filter::OPERATOR_SUB_FILTER);
        $query->order->addOrder(Default_Model_Simple::QUERY_ID,
                Core_Model_Order::ORDER_DESC);
        $simpleEntities = Default_Model_Simple::loadList($query);
        $this->assertEquals(4, count($simpleEntities));
        $this->assertEquals(4, Default_Model_Simple::countTotal($query));
        $this->assertSame($simpleEntities[0], $this->_simpleEntities[5]);
        $this->assertSame($simpleEntities[1], $this->_simpleEntities[4]);
        $this->assertSame($simpleEntities[2], $this->_simpleEntities[1]);
        $this->assertSame($simpleEntities[3], $this->_simpleEntities[0]);
    }

    /**
     * Méthode appelée à la fin des test.
     */
    protected function tearDown()
    {
        foreach ($this->_simpleEntities as $simpleEntity) {
            $simpleEntity->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Méthode appelée à la fin de tous les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Default_Model_Simple en base, sinon suppression !
        if (Default_Model_Simple::countTotal() > 0) {
            echo PHP_EOL . 'Des SimpleEntity restantes ont été trouvé après les tests, suppression en cours !';
            foreach (Default_Model_Simple::loadList() as $simpleEntity) {
                $simpleEntity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}
