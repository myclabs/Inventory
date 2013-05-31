<?php
/**
 * Class Orga_Test_ProjectTest
 * @author valentin.claras
 * @author maxime.fourt
 * @package    Orga
 * @subpackage Test
 */

/**
 * Test Project Class.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_ProjectTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_ProjectSetUp');
        $suite->addTestSuite('Orga_Test_ProjectOthers');
        return $suite;
    }

    /**
     * Generation of a test object
     *
     * @return Orga_Model_Project
     */
    public static function generateObject()
    {
        $o = new Orga_Model_Project();
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     *
     * @param Orga_Project $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test des méthodes de base de l'objet métier Orga_Model_Project
 * @package Orga
 */
class Orga_Test_ProjectSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Methode exécuter avant chaque test
     */
     public static  function setUpBeforeClass()
     {
        // Vérification qu'il ne reste aucun Orga_Model_Project en base, sinon suppression !
        if (Orga_Model_Project::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Project restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Project::loadList() as $project) {
                $project->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
     }

    /**
     * Test le constructeur.
     *
     * @return Orga_Model_Project
     */
    function testConstruct()
    {
        $o = new Orga_Model_Project();
        $this->assertInstanceOf('Orga_Model_Project', $o);
        $this->assertEquals($o->getKey(), array());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Orga_Model_Project $o
     */
    function testLoad(Orga_Model_Project $o)
    {
         $oLoaded = Orga_Model_Project::load($o->getKey());
         $this->assertInstanceOf('Orga_Model_Project', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Orga_Model_Project $o
     */
    function testDelete(Orga_Model_Project $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Project en base, sinon suppression !
        if (Orga_Model_Project::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Project restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Project::loadList() as $project) {
                $project->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}

/**
 * Test des fonctionnalités de l'objet métier Orga_Model_Project
 * @package Orga
 */
class Orga_Test_ProjectOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Project
     */
    protected $project;

    /**
     * Méthode appelée avant les tests
     */
    public static  function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Project en base, sinon suppression !
        if (Orga_Model_Project::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Project restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Project::loadList() as $project) {
                $project->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Function called before each test.
     */
    protected function setUp()
    {
        // Create a test object
        $this->project = Orga_Test_ProjectTest::generateObject();
    }

    /**
     * Test the project function to order granularities.
     */
    public function testOrderGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis($this->project);
        $axis1->setRef('RefOrderGranularities1');
        $axis1->setLabel('LabelOrderGranularities1');

        $axis11 = new Orga_Model_Axis($this->project);
        $axis11->setRef('RefOrderGranularities11');
        $axis11->setLabel('LabelOrderGranularities11');
        $axis11->setDirectNarrower($axis1);

        $axis111 = new Orga_Model_Axis($this->project);
        $axis111->setRef('RefOrderGranularities111');
        $axis111->setLabel('LabelOrderGranularities111');
        $axis111->setDirectNarrower($axis11);

        $axis12 = new Orga_Model_Axis($this->project);
        $axis12->setRef('RefOrderGranularities12');
        $axis12->setLabel('LabelOrderGranularities12');
        $axis12->setDirectNarrower($axis1);

        $axis121 = new Orga_Model_Axis($this->project);
        $axis121->setRef('RefOrderGranularities121');
        $axis121->setLabel('LabelOrderGranularities121');
        $axis121->setDirectNarrower($axis12);

        $axis122 = new Orga_Model_Axis($this->project);
        $axis122->setRef('RefOrderGranularities122');
        $axis122->setLabel('LabelOrderGranularities122');
        $axis122->setDirectNarrower($axis12);

        $axis123 = new Orga_Model_Axis($this->project);
        $axis123->setRef('RefOrderGranularities123');
        $axis123->setLabel('LabelOrderGranularities123');
        $axis123->setDirectNarrower($axis12);

        $axis2 = new Orga_Model_Axis($this->project);
        $axis2->setRef('RefOrderGranularities2');
        $axis2->setLabel('LabelOrderGranularities2');

        $axis21 = new Orga_Model_Axis($this->project);
        $axis21->setRef('RefOrderGranularities21');
        $axis21->setLabel('LabelOrderGranularities21');
        $axis21->setDirectNarrower($axis2);

        $axis3 = new Orga_Model_Axis($this->project);
        $axis3->setRef('RefOrderGranularities3');
        $axis3->setLabel('LabelOrderGranularities3');

        $axis31 = new Orga_Model_Axis($this->project);
        $axis31->setRef('RefOrderGranularities31');
        $axis31->setLabel('LabelOrderGranularities31');
        $axis31->setDirectNarrower($axis3);

        $axis311 = new Orga_Model_Axis($this->project);
        $axis311->setRef('RefOrderGranularities311');
        $axis311->setLabel('LabelOrderGranularities311');
        $axis311->setDirectNarrower($axis31);

        $axis312 = new Orga_Model_Axis($this->project);
        $axis312->setRef('RefOrderGranularities312');
        $axis312->setLabel('LabelOrderGranularities312');
        $axis312->setDirectNarrower($axis31);

        $axis32 = new Orga_Model_Axis($this->project);
        $axis32->setRef('RefOrderGranularities32');
        $axis32->setLabel('LabelOrderGranularities32');
        $axis32->setDirectNarrower($axis3);

        $axis33 = new Orga_Model_Axis($this->project);
        $axis33->setRef('RefOrderGranularities33');
        $axis33->setLabel('LabelOrderGranularities33');
        $axis33->setDirectNarrower($axis3);

        $axis331 = new Orga_Model_Axis($this->project);
        $axis331->setRef('RefOrderGranularities331');
        $axis331->setLabel('LabelOrderGranularities331');
        $axis331->setDirectNarrower($axis33);

        $axis332 = new Orga_Model_Axis($this->project);
        $axis332->setRef('RefOrderGranularities332');
        $axis332->setLabel('LabelOrderGranularities332');
        $axis332->setDirectNarrower($axis33);

        $granularity0 = new Orga_Model_Granularity($this->project);

        $granularity1 = new Orga_Model_Granularity($this->project, [$axis11, $axis122, $axis311]);

        $granularity2 = new Orga_Model_Granularity($this->project, [$axis1, $axis31]);

        $granularity3 = new Orga_Model_Granularity($this->project, [$axis2]);

        $granularity4 = new Orga_Model_Granularity($this->project, [$axis1, $axis3]);

        $granularity5 = new Orga_Model_Granularity($this->project, [$axis12, $axis21, $axis33]);

        $this->assertEquals(1, $granularity0->getPosition()); // 6
        $this->assertEquals(4, $granularity1->getPosition()); // 3
        $this->assertEquals(5, $granularity2->getPosition()); // 4
        $this->assertEquals(2, $granularity3->getPosition()); // 2
        $this->assertEquals(6, $granularity4->getPosition()); // 5
        $this->assertEquals(3, $granularity5->getPosition()); // 1

        $this->assertEquals($this->project->getGranularities(), array($granularity0, $granularity1, $granularity2, $granularity3, $granularity4, $granularity5));
    }

    /**
     * Function tearDown
     *  Fonction appelee apres chaque test
     */
    protected function tearDown()
    {
        Orga_Test_ProjectTest::deleteObject($this->project);
    }

    /**
     * Véfirifie si la table est vide et supprime les mots clés créés dans keyword
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Project en base, sinon suppression !
        if (Orga_Model_Project::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Project restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Project::loadList() as $project) {
                $project->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}
