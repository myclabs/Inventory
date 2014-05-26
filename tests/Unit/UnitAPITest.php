<?php

namespace Tests\Unit;

use Core\Test\TestCase;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use MyCLabs\UnitAPI\Operation\Result\AdditionResult;
use MyCLabs\UnitAPI\Operation\Result\MultiplicationResult;
use Unit\UnitAPI;

/**
 * @author valentin.claras
 * @author hugo.charboniere
 * @author yoann.croizer
 */
class UnitAPITest extends TestCase
{
    /**
     * Test de la fonction getSymbol()
     * On vérigfie que le symbol est bien le bon
     */
    public function testGetSymbol()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new UnitAPI('m^2.animal^-1.m^-2.g.g_co2e^2');
        $this->assertSame('m2.g.g equ. CO22/animal.m2', $o->getSymbol()->get('fr'));
    }

    /**
     * Test de la fonction getNormalizedUnit()
     * On vérigfie que le symbol est bien le bon
     */
    public function testGetNormalizedUnit()
    {
        //Traitement d'un cas assez complexe utilisant tout les types d'unité (discrète, étendue et standard)
        $o = new UnitAPI('g.An');
        $result = $o->getNormalizedUnit();
        $this->assertTrue($result instanceof UnitAPI);
        $this->assertSame('kg.s', $result->getRef());
    }

    /**
     * Test de la fonction isEquivalent()
     * On vérifie que deux unités equivalentes le son bien et inversement
     */
    public function testIsEquivalent()
    {
        //Cas ou l'on mélange plusieurs type d'unité.
        $unit1 = new UnitAPI('m^2.animal^-1.m^-2.kg.m^2.J^-5.kg_co2e^2');
        $unit2 = new UnitAPI('animal^-1.g.m^2.J^-5.g_co2e^2');
        $this->assertTrue($unit1->isEquivalent($unit2->getRef()));

        $unit3 = new UnitAPI('animal^-1.g.m');
        $this->assertFalse($unit1->isEquivalent($unit3->getRef()));

        // Cas ou l'on compare seulement des unités standard
        $unit4 = new UnitAPI('g');
        $this->assertTrue($unit4->isEquivalent($unit4->getRef()));

        // Cas ou l'on compare seulement des unités pas standard.
        $unit5 = new UnitAPI('animal');
        $this->assertTrue($unit5->isEquivalent($unit5->getRef()));

        $this->assertFalse($unit1->isEquivalent(''));
    }

    /**
     * Test de la fonction getConversionFactor()
     * On test si les facteurs de conversion retournés sont justes
     */
    public function testGetConversionFactor()
    {
        $unit1 = new UnitAPI('m^2.animal^-1.m^-2.kg');
        $result = $unit1->getConversionFactor(new UnitAPI('m^2.animal^-1.m^-2.kg'));
        $this->assertEquals(1, $result);

        $unit1 = new UnitAPI('kg^2.g');
        $result = $unit1->getConversionFactor(new UnitAPI('kg^3'));
        $this->assertEquals(0.001, $result);
    }

    /**
     * Test de la fonction testMultiply()
     * On vérifie que le résultat d'une multiplication est correcte
     * pour une multiplication
     * pour une division
     */
    public function testMultiply()
    {
        $operande[0]['unit'] = new UnitAPI('g.animal^-1.kg.kg_ce');
        $operande[0]['signExponent'] = 1;
        $operande[1]['unit'] = new UnitAPI('animal.s.an.kg^-1');
        $operande[1]['signExponent'] = -1;

        $result = UnitAPI::multiply($operande);
        $this->assertTrue($result instanceof MultiplicationResult);
        $this->assertEquals('kg^3.kg_co2e.s^-2.animal^-2', $result->getUnitId());
    }

    /**
     * Test de la fonction getCalculateSum()
     * On vérifie que le résultat d'une somme est correcte
     * pour une addition
     * pour une soustraction
     */
    public function testCalculateSum()
    {
        $operande[] = 'g.animal^-1.kg.g_co2e';
        $operande[] = 'animal.s.an.kg^-1';
        $unit = new UnitAPI();

        try {
            $unit->calculateSum($operande);
        } catch (IncompatibleUnitsException $e) {
            $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        $operande = null;
        $operande[] = 'g.animal^-1.kg.an^2';
        $operande[] = 'animal^-1.s.an.kg^2';

        $result = $unit->calculateSum($operande);

        $this->assertTrue($result instanceof AdditionResult);
        $this->assertEquals('kg^2.s^2.animal^-1', $result->getUnitId());
    }

    /**
     * Test de la fonction getCompatibleUnits()
     */
    public function testGetCompatibleUnitsStandardUnit()
    {
        $unit1 = new UnitAPI('kg');
        $results = $unit1->getCompatibleUnits();
        $this->assertCount(1, $results);
        $this->assertContains(new UnitAPI('g'), $results, null, false, false);
    }

    /**
     * Test de la fonction getCompatibleUnits()
     */
    public function testGetCompatibleUnitsComposedUnit()
    {
        $unit2 = new UnitAPI('kg.s^2');
        $results = $unit2->getCompatibleUnits();
        $this->assertCount(3, $results);
        $this->assertContains(new UnitAPI('kg.an^2'), $results, null, false, false);
        $this->assertContains(new UnitAPI('g.s^2'), $results, null, false, false);
        $this->assertContains(new UnitAPI('g.an^2'), $results, null, false, false);
    }
}
