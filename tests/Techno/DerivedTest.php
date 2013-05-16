<?php
/**
 * Creation of the Techno Derived test.
 * @package Techno
 */

/**
 * Test Techno package.
 * @package Techno
 */
class Techno_DerivedTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('TechnoDerivedSetUpTest');
//        $suite->addTestSuite('TechnoDerivedMetierTest');
        return $suite;
    }
    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @return Techno_Model_Relation_Derived
     */
    public static function generateObject()
    {
        // Ajout du Upstream
        $upstreamProcess = new Techno_Model_Element_Process();

        $calcValue1 = new Calc_Value();
        $calcValue1->digitalValue = 25;
        $calcValue1->relativeUncertainty = 10;

        $upstreamProcess->setValue($calcValue1);
        $upstreamProcess->save();

        // Ajout du Downstream
        $downstreamProcess = new Techno_Model_Element_Process();
        $calcValue2 = new Calc_Value();
        $calcValue2->digitalValue = 15;
        $calcValue2->relativeUncertainty = 25;
        $downstreamProcess->setlValue($calcValue2);
        $downstreamProcess->save();

        // Création de la source
        $source = new Techno_Model_Relation_Source();
        $source->setUpstreamProcess($upstreamProcess);
        $source->setDownstreamProcess($downstreamProcess);
        $source->setType(1);
        $source->save();

        $derived = new Techno_Model_Relation_Derived();
        $derived->setUpstreamProcess($upstreamProcess);
        $derived->setDownstreamProcess($downstreamProcess);
        $derived->setSource($source);
        $derived->save();

        $source->delete();
        $downstreamProcess->delete();
        $upstreamProcess->delete();
        return $derived;
    }

    /**
     * Génere un objet dérivé prêt à l'emploi pour les tests.
     * @param Techno_Model_Relation_Derived $derived
     */
    public static function deleteObject($derived)
    {
        $derived->delete();
    }
}

/**
 * Test des fonctionnalités de la structure Techno_Model_Relation_Derived
 * @package Techno
 */
class TechnoDerivedSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Function testConstruct
     *  Test des constructeurs et de la sauvegarde en base de données
     */
    function testConstruct()
    {
        $derived = new Techno_Model_Relation_Derived();
        // Ajout du Upstream
        $upstreamProcess = new Techno_Model_Element_Process();
        $calcValue = new Calc_Value();
        $calcValue->digitalValue = 25;
        $calcValue->relativeUncertainty = 15;
        $upstreamProcess->setValue($calcValue);
        $upstreamProcess->save();

        $derived->setUpstreamProcess($upstreamProcess);

        // Ajout du Downstream
        $downstreamProcess = new Techno_Model_Element_Process();

        $calcValue2 = new Calc_Value();
        $calcValue2->digitalValue = 15;
        $calcValue2->relativeUncertainty = 22;

        $downstreamProcess->setValue($calcValue2);
        $downstreamProcess->save();

        $derived->setDownstreamProcess($downstreamProcess);

        // Création de la source
        $source = new Techno_Model_Relation_Source();
        $source->setDownstreamProcess($downstreamProcess);
        $source->setUpstreamProcess($upstreamProcess);
        // Ajout du type
        $source->setType(1);
        $source->save();

        $derived->setSource($source);// Ajout de la source
        $source->delete();
        $upstreamProcess->delete();
        $downstreamProcess->delete();
        return $derived;
    }

    /**
     * Test de save
     * @param Techno_Model_Relation_Derived $derived
     * @return Techno_Model_Relation_Derived
     * @depends testConstruct
     */
    function testSave($derived)
    {
        $derived->save();
        $this->assertNotNull($derived->getKey(), "Object id is not defined");
        return $derived;
    }

    /**
     * @param Techno_Model_Relation_Derived $derived
     * @return Techno_Model_Relation_Derived
     * @depends testSave
     */
    function testDelete($derived)
    {
        $derived->delete();
        $this->assertNull($derived->getKey());
    }


}

/**
 * Test des fonctionnalités de l'objet métier Techno_Model_Relation_Derived
 * @package Techno
 */
class TechnoDerivedMetierTest extends PHPUnit_Framework_TestCase
{

    /**
     * Function testsetgetSource()
     *  Méthode de tests pour la source
     */
    function testsetgetSource()
    {
        $this->markTestIncomplete();
//         $derived = new Techno_Model_Relation_Derived();
//         // Ajout du Downstream
//         $downstreamProcess = new Techno_Model_Element_Process();
//         $downstreamProcess->setDigitalValue(15);
//         $downstreamProcess->setRelativeUncertainty(40);
//         $downstreamProcess->save();
//         $derived->setDownstreamProcess($downstreamProcess);

//         // Création de la source
//         $source = new Techno_Model_Relation_Source();
//         // Ajout du Upstream
//         $upstreamProcess = new Techno_Model_Element_Process();
//         $upstreamProcess->setDigitalValue(25);
//         $upstreamProcess->setRelativeUncertainty(10);
//         $upstreamProcess->save();
//         $source->setUpstreamProcess($upstreamProcess);

//         // Ajout du Downstream
//         $downstreamProcess = new Techno_Model_Element_Process();
//         $downstreamProcess->setDigitalValue(15);
//         $downstreamProcess->setRelativeUncertainty(40);
//         $downstreamProcess->save();
//         $source->setDownstreamProcess($downstreamProcess);

//         // Ajout du type
//         $source->setType(1);
//         $source->save();
//         $derived->setSource($source);// Ajout de la source

//         // Vérification de la source
//         $this->assertSame($derived->getSource(), $source);
//         $source->delete();
//         $upstreamProcess->delete();
//         $downstreamProcess->delete();
    }

    /**
     * Function testsetGetUpStreamProcess
     *  Méthode de tests pour le Upstream Process
     */
    function testsetgetUpstreamProcess()
    {
        $this->markTestIncomplete();
//        $derived = new Techno_Model_Relation_Derived();
//        $upstream = new Techno_Model_Element_Process();
//
//        $calcValue = new Calc_Value();
//        $calcValue->digitalValue = 10;
//        $calcValue->relativeUncertainty = 15;
//
//        $upstream->setValue($calcValue);
//
//        $upstream->save();
//        $derived->setUpstreamProcess($upstream);
//        $this->assertSame($derived->getUpstreamProcess(), $upstream);
//        $upstream->delete();
    }

    /**
     * Function testsetgetDownStream Process
     *  Méthode de tests pour le Downstream Process
     */
    function testsetgetDownstreamProcess()
    {
        $this->markTestIncomplete();
//        $derived = new Techno_Model_Relation_Derived();
//        $downstream = new Techno_Model_Element_Process();
//
//        $calcValue = new Calc_Value();
//        $calcValue->digitalValue = 10;
//        $calcValue->relativeUncertainty = 15;
//
//        $downstream->setValue($calcValue);
//
//        $downstream->save();
//        $derived->setDownstreamProcess($downstream);
//        $this->assertSame($derived->getDownstreamProcess(), $downstream);
//        $downstream->delete();
    }

    /**
     * Function testAddCoeff()
     *  Méthode de tests d'ajout et suppression de coefficients
     */
    function testaddCoeff() {
        $this->markTestIncomplete();

//         $coeff = new Techno_Model_Element_Coeff();
//         $coeff->setDigitalValue(25);
//         $coeff->setRelativeUncertainty(10);
//         $coeff->save();

//         $derived = Techno_DerivedTest::generateObject();
//         $derived->addCoeff($coeff, 2);
//         $derived->save();

//         // Test de la présence de l'élément ajouté
//         $derivedCoeffs = $derived->getDerivedCoeffs();
//         $this->assertEquals(count($derivedCoeffs),1);

//         // Test de la valeur de l'élément ajouté
//         $this->assertSame($derivedCoeffs[0]->getCoeff(), $coeff);// Pour le coeff
//         $this->assertEquals($derivedCoeffs[0]->getExponent(), 2);// Pour l'exposant

//         $derived->delete();
//         $coeff->delete();
    }

    /**
     * Function testremoveCoeff
     *  Méthode de tests d'ajout et suppression de coefficients
     * @mar
     */
    function testremoveCoeff() {
        $this->markTestIncomplete();
//         $coeff = new Techno_Model_Element_Coeff();
//         $coeff->setDigitalValue(25);
//         $coeff->setRelativeUncertainty(10);
//         $coeff->save();

//         $derived = Techno_DerivedTest::generateObject();
//         $derived->addCoeff($coeff, 2);
//         $derived->save();

//         // Test de la présence de l'élément ajouté
//         $derivedCoeffs = $derived->getDerivedCoeffs();

//         // Test de la valeur de l'élément ajouté

//         // Suppression du Coeff
//         $derived->removeCoeff($coeff);
//         $derived->save();

//         // Test de la suppression de l'élément ajouté
//         $derivedCoeffs = $derived->getDerivedCoeffs();
//         $this->assertEquals(count($derivedCoeffs),0);

//         $derived->delete();
//         $coeff->delete();
    }

    /**
     * Vérifie si la table est vide et supprime les mots clés, les listes
     */
    public static function tearDownAfterClass()
    {
//         $listprocess = Techno_Model_Element_Process::loadlist();

//         if ($listprocess != null) {
//             foreach ($listprocess as $process) {
//                 $process->delete();
//             }
//         }

        // Va supprimer les familles créées dans le setUpBeforeClass et les dimensions qui lui sont associés...
//         $listFamily = Techno_Model_Family::loadList();

//         if ($listFamily != null) {
//             foreach ($listFamily as $familly) {
//                 $familly->delete();
//             }
//         }

//        Zend_Registry::set('desactiverMultiton', true);
//        if (! Techno_Model_DAO_Family::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Family n'est pas vide apres les tests\n";
//        }
//        if (! Techno_Model_Family_DAO_Cell::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Cell n'est pas vide apres les tests\n";
//        }
//        if (! Techno_Model_DAO_CellTags::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table CellTags n'est pas vide apres les tests\n";
//        }
//        if (! Techno_Model_DAO_FamilyTags::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table FamilyTags n'est pas vide apres les tests\n";
//        }
    }

}
