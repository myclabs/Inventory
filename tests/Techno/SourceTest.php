<?php
/**
 * Creation of the Techno Coeff test.
 * @package Techno
 */

/**
 * Test Techno package.
 * @package Techno
 */
class Techno_SourceTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('TechnoSourceMetierTest');
        return $suite;
    }

    /**
     * Génere un objet source prêt à l'emploi pour les tests.
     * @return Techno_Model_Project_Source
     */
    public static function generateObject()
    {
        $source = new Techno_Model_Relation_Source();
        // Ajout du Upstream
        $upstreamProcess = new Techno_Model_Element_Process();
        $upstreamProcess->setDigitalValue(25);
        $upstreamProcess->setRelativeUncertainty(10);
        $upstreamProcess->save();
        $source->setUpstreamProcess($upstreamProcess);
        // Ajout du Downstream
        $downstreamProcess = new Techno_Model_Element_Process();
        $downstreamProcess->setDigitalValue(15);
        $downstreamProcess->setRelativeUncertainty(40);
        $downstreamProcess->save();
        $source->setDownstreamProcess($downstreamProcess);
        // Ajout du type
        $source->setType(1);

        $source->save();
        $upstreamProcess->delete();
        $downstreamProcess->delete();
        return $source;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Techno_Model_Relation_Source &$source
     */
    public static function deleteObject(& $source)
    {
        // Suppression de l'objet.
        $source->delete();
    }
}

/**
 * Test des fonctionnalités de l'objet métier Techno_Model_Coeff
 * @package Techno
 */
class TechnoSourceMetierTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant les tests
     * Création d'un jeu de données
     */
    public static function setUpBeforeClass()
    {
        Zend_Registry::set('desactiverMultiton', false);

    }

    /**
     * Fonction testgetsetTypeUndefinedAttribute
     *  en fonctionnement normal
     *  @expectedException Core_Exception_UndefinedAttribute
     */
    function testsetgetTypeUndefinedAttribute() {
        $source = new Techno_Model_Relation_Source();
        $source->getType();
    }

    /**
     * Fonction testaddremoveCoeff
     *  en fonctionnement normal
     */
    function testaddremoveCoeff() {

        $this->markTestIncomplete();

        $coeff = new Techno_Model_Element_Coeff();
        $coeff->setDigitalValue(25);
        $coeff->setRelativeUncertainty(10);
        $coeff->save();

        $source = Techno_SourceTest::generateObject();
        $source->addCoeff($coeff, 2);

        // Test de la présence de l'élément ajouté
        $sourceCoeffs = $source->getSourceCoeffs();
        $this->assertEquals(count($sourceCoeffs),1);

        // Test de la valeur de l'élément ajouté
        $this->assertSame($sourceCoeffs[0]->getCoeff(), $coeff);// Pour le coeff
        $this->assertEquals($sourceCoeffs[0]->getExponent(), 2);// Pour l'exposant

        // Suppression du Coeff
        $source->removeCoeff($coeff);

        // Test de la suppression de l'élément ajouté
        $sourceCoeffs = $source->getSourceCoeffs();
        $this->assertEquals(count($sourceCoeffs),0);

        $source->delete();
        $coeff->delete();
    }

    /**
     * Fonction testaddremoveRelationDerived
     * en fonctionnement normal
     */
    function testaddremoveRelationDerived() {
        $this->markTestIncomplete();

//         $upstreamprocess = new Techno_Model_Element_Coeff();

//         $derived = new Techno_Model_Relation_Derived();
//         $upstreamProcess = new Techno_Model_Element_Process();
//         $upstreamProcess->setDigitalValue(2);
//         $upstreamProcess->setRelativeUncertainty(5);
//         $upstreamProcess->save();
//         $derived->setUpstreamProcess($upstreamProcess);

//         $downstreamProcess = new Techno_Model_Element_Process();
//         $downstreamProcess->setDigitalValue(30);
//         $downstreamProcess->setRelativeUncertainty(63);
//         $downstreamProcess->save();
//         $derived->setDownstreamProcess($upstreamProcess);

//         // Ajout dans la source
//         $source = Techno_SourceTest::generateObject();
//         $derived->setSource($source);
//         $derived->save();

//         $source->addDerivedRelation($derived);
//         // Test de la présence d'une nouvelle relation dérivée
//         $this->assertEquals(count($source->getDerivedRelations()), 1);

//         $upstreamProcess->delete();
//         $downstreamProcess->delete();
//         $derived->delete();
    }

    /**
     * Fonction testhasCoeff
     * en fonctionnement normal
     */
    function testhasCoeff() {
        $this->markTestIncomplete();
        $coeff = new Techno_Model_Element_Coeff();
        $coeff->setDigitalValue(25);
        $coeff->setRelativeUncertainty(10);
        $coeff->save();

        $source = new Techno_Model_Relation_Source();
        $source->addCoeff($coeff, 2);
        $this->assertEquals($source->hasCoeff($coeff), true);
        $coeff->delete();
    }

    /**
     * Fonction testgetsetType
     *  en fonctionnement normal
     */
    function testsetgetType()
    {
        $source = new Techno_Model_Relation_Source();
        $source->setType(2);
        $this->assertEquals($source->getType(), 2);
    }

    /**
     * Fonction testgetsetUpstreamProcess
     * en fonctionnement normal
     */
    function testsetgetUpstreamProcess()
    {
        $this->markTestIncomplete();
        $source = new Techno_Model_Relation_Source();
        $upstreamProcess = new Techno_Model_Element_Process();
        $upstreamProcess->setDigitalValue(25);
        $upstreamProcess->setRelativeUncertainty(10);
        $upstreamProcess->save();
        $source->setUpstreamProcess($upstreamProcess);
        $source->setType($upstreamProcess->getKey());

        $this->assertSame($source->getUpstreamProcess(), $upstreamProcess);
        $upstreamProcess->delete();

    }

    /**
     * Fonction testgetsetDownstreamProcess
     * en fonctionnement normal
     */
    function testsetgetDownstreamProcess()
    {
        $this->markTestIncomplete();
        $source = new Techno_Model_Relation_Source();
        $downstreamProcess = new Techno_Model_Element_Process();
        $downstreamProcess->setDigitalValue(25);
        $downstreamProcess->setRelativeUncertainty(10);
        $downstreamProcess->save();
        $source->setDownstreamProcess($downstreamProcess);

        $this->assertSame($source->getDownstreamProcess(), $downstreamProcess);
        $downstreamProcess->delete();

    }

    /**
     * Vérifie si la table est vide et supprime les mots clés, les listes
     */
    public static function tearDownAfterClass()
    {
        // Va supprimer les familles créées dans le setUpBeforeClass et les dimensions qui lui sont associés...
//         $listFamily = Techno_Model_Family::loadList();

//         foreach ($listFamily as $familly) {
//             $familly->delete();
//         }

//        Zend_Registry::set('desactiverMultiton', true);
//        if (! Techno_Model_DAO_Family::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Family n'est pas vide apres les tests\n";
//        }
//
//        if (! Techno_Model_Family_DAO_Cell::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Cell n'est pas vide apres les tests\n";
//        }
//
//        if (! Techno_Model_DAO_CellTags::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table CellTags n'est pas vide apres les tests\n";
//        }
//
//        if (! Techno_Model_DAO_FamilyTags::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table FamilyTags n'est pas vide apres les tests\n";
//        }
    }

}
