<?php
/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 * @package AF
 */

/**
 * @package Algo
 */
class AFTest
{
    public static $numContextLabel = 1;

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('AFSetUpTest');
//        $suite->addTestSuite('AFOtherTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @return AF_Model_AF
     */
    public static function generateObject()
    {
        $group = Form_GroupTest::generateObject();

        $context = new Classif_Model_Context();
        $context->setLabel('testlabel'.self::$numContextLabel);
        self::$numContextLabel++;
        $context->save();

        $o = new AF_Model_AF();
        $o->setRef('test');
        $o->setContext($context);
        $o->setRootGroup($group);
        $o->save();
        return ($o);
    }


    /**
     * Supprime un objet utilisé dans les tests
     * @param AF_Model_AF $o
     */
    public static function deleteObject(AF_Model_AF $o)
    {
        // On ne peut pas supprimer un AF possédant des inputs donc on les supprimes
        foreach ($o->getInputSets() as $inputSet ) {
            $inputSet->delete();
        }
        $o->delete();
    }

}

/**
 * AFSetUpTest
 * @package Algo
 */
class AFSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test du constructeur de AF
     * @return AF_Model_AF
     */
    function testConstruct()
    {
        $o = new AF_Model_AF(strtolower(Core_Tools::generateString(20)));
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_AF $o
     * @return AF_Model_AF
     */
    function testLoad(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_AF $o
     */
    function testDelete(AF_Model_AF $o)
    {
        $this->assertTrue($o instanceof AF_Model_AF);
    }

}

/**
 * Extended tests
 * @package AF
 */
class AFOtherTest extends PHPUnit_Framework_TestCase
{
    /**
     * Un AF
     * @var AF_Model_AF
     */
    protected $_af;
    protected $_cond1;
    protected $_cond2;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
        AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
        AF_Model_DAO_AF::getInstance()->unitTestsClearTable();

        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_DAO_Numeric::getInstance()->unitTestsClearTable();
//        AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsClearTable();
//
//        AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//        AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();
//
        AF_Model_DAO_Condition::getInstance()->unitTestsClearTable();
        AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        AF_Model_Condition_Elementary_DAO_Numeric::getInstance()->unitTestsClearTable();
//
//        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//        Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
//
//        Algo_Model_DAO_Set::getInstance()->unitTestsClearTable();
//
//        Tree_Model_DAO_Component::getInstance()->unitTestsClearTable();
    }

     /**
      * Function called before each test
      */
     protected function setUp()
     {
        try {
            $this->_af = AFTest::generateObject();
        } catch (Exception $e) {
            $this->fail($e);
        }
     }

     /**
      * Test setLabel et getLabel
      * @expectedException Core_Exception_InvalidArgument
      */
     function testSetGetLabel()
     {
         $this->_af->setLabel('labelTest');
         $this->assertEquals('labelTest', $this->_af->getLabel());

         // Test de l'exception levée si le paramètre de setLabel est incorrect
         $this->_af->setLabel(true);
     }

     /**
      * Test setHelp et getHelp
      * @expectedException Core_Exception_InvalidArgument
      */
     function testSetGetHelp()
     {
         $this->_af->setHelp('helpTest');
         $this->assertEquals('helpTest', $this->_af->getHelp());

         // Test de l'exception levée si le paramètre de setLabel est incorrect
         $this->_af->setHelp(true);
     }

     /**
      * Test setDocumentation et getDocumentation
      * @expectedException Core_Exception_InvalidArgument
      */
     function testSetGetDocumentation()
     {
         $this->_af->setDocumentation('helpTest');
         $this->assertEquals('helpTest', $this->_af->getDocumentation());

       // Test de l'exception levée si le paramètre de setLabel est incorrect
         $this->_af->setDocumentation(true);
     }

     /**
      * Test setRootGroup et getRootGroup
      * @expectedException Core_Exception_InvalidArgument
      */
     function testSetGetRoot()
     {
         //on supprime le groupe lié à l'af dans la méthode generate
         $deadGroup = $this->_af->getRootGroup();

         //on associe un autre groupe pour le test
         $group = Form_GroupTest::generateObject();
         $this->_af->setRootGroup($group);

         $this->assertSame($group, $this->_af->getRootGroup());
         $this->assertNotSame($deadGroup, $this->_af->getRootGroup());

         Form_GroupTest::deleteObject($deadGroup);

         // On test l'exception levée si on passe en paramètre de la méthode un groupe sans id.
         $group = new AF_Model_Component_Group();
         $this->_af->setRootGroup($group);
     }

     /**
      * Test getContext et setContext
      * @expectedException Core_Exception_InvalidArgument
      */
     function testSetGetContext()
     {
         //on supprime le context associé à l'af dans la méthode generate
         $deadContext = $this->_af->getContext();

         //on associe un autre context pour le test
         $context = new Classif_Model_Context();
         $context->setLabel('context2');
         $context->save();

         $this->_af->setContext($context);

         $this->assertEquals($context->getKey(), $this->_af->getContext()->getKey());
         $this->assertNotEquals($deadContext->getKey(), $this->_af->getContext()->getKey());

         // On vide la les tables (obligatoire de faire comme cela)
         $this->_af->setContext($deadContext);
         $context->delete();

         // On test l'exception levée si on passe en paramètre de la méthode un context sans id.
         $context = new Classif_Model_Context();
         $this->_af->setContext($context);
     }

     /**
      * Test getComments
      */
     function testGetComments()
     {
     }

     /**
      * Test addComment
      */
     function testAddComment()
     {
     }

     /**
      * Test getElements
      */
     function testGetElements()
     {
     }

     // Tous les tests précédents fonctionnent !
     // @todo les tests en dessous posent problème, à corriger

//     /**
//      * Test getElementsByType
//      */
//     function testGetElementsByType()
//     {
//
//       // On doit commencer par associer notre groupe racine à un noeud de tree.
//         $rootGroup = AF_Model_Component_Group::load($this->_af->getRootGroup());
//
//         $rootTree = new Tree_Model_Composite();
//         $rootTree->setEntity($rootGroup);
//         $rootTree->save();
//
//         $rootGroup->setTreeComponent($rootTree);
////         $rootGroup->ref = 'rootgroup';
////         $rootGroup->setAf($this->_af);
//         $rootGroup->save();
//
//         // On mets un groupe dans notre groupe racine pour vérifier que la méthode parcourt bien tout l'arbre.
//        $group    = Form_GroupTest::generateObject();
//
//        $rootNode = Tree_Model_Component::load($rootGroup->getTreeComponent());
//
//        $tree = new Tree_Model_Composite();
//        $tree->setEntity($group);
//        $tree->setParent($rootNode);
//        $tree->save();
//
////         $groupTree = new Tree_Model_Composite();
////         $groupTree->setEntity($group);
////         $groupTree->setParent($rootTree);
////         $groupTree->save();
//
//         $group->setTreeComponent($tree);
//         $group->save();
//
//
//         // On test
//         $groups = $this->_af->getElementsByType('AF_Model_Component_Group');
//         $this->assertEquals(count($groups), 2);
//         $this->assertEquals($rootGroup, $groups[0]);
//         $this->assertEquals($group, $groups[1]);
//
//     }
//
//
//     /**
//      * Test la méthode de génération des formulaires
//      */
//     function testGenerateForm()
//     {
//       // On supprime le groupe lié à l'af set dans la méthode generate
//       $deadGroup = AF_Model_Component_Group::load($this->_af->getRootGroup());
//       Form_GroupTest::deleteObject($deadGroup);
//
//       // On créé un arbre de component.
//       $group      = new AF_Model_Component_Group();
//       $group->ref = 'unGroupe';
//       $group->save();
//
//       $treeGroup = new Tree_Model_Composite();
//       $treeGroup->setEntity($group);
//       $treeGroup->save();
//
//       $group->setTreeComponent($treeGroup);
//       $group->save();
//
//       $value = new Calc_Value();
//       $value->relativeUncertainty = 1;
//       $value->digitalValue        = 1;
//
//       $numericInput      = new AF_Model_Form_NumericInput();
//       $numericInput->ref = 'numericInput1';
//       $numericInput->setValue($value);
//       $numericInput->save();
//
//       $treeNumeric = new Tree_Model_Leaf();
//       $treeNumeric->setEntity($numericInput);
//       $treeNumeric->setParent($treeGroup);
//       $treeNumeric->save();
//
//       $this->_af->setRootGroup($group);
//       $this->_af->save();
//
//       $result = $this->_af->generateForm();
//
//       $this->assertTrue($result instanceof UI_Form);
//
//     }

     /**
      * Function called after each test
      */
     protected function tearDown()
     {
         try {
            AFTest::deleteObject($this->_af);
         } catch (Exception $e) {
            $this->fail($e);
         }
     }

}
