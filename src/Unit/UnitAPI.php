<?php

namespace Unit;

use DI\Container;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\OperationService;
use MyCLabs\UnitAPI\UnitService;
use Unit\Domain\ComposedUnit\ComposedUnit;

/**
 * API
 * @author     valentin.claras
 * @author     hugo.charbonnier
 * @author     yoann.croizer
 */
class UnitAPI
{
    /**
     * Référent textuel d'une unité
     *  si le référent contient un "." c'et qu'il s'agit d'une unité composée.
     * @var string
     */
    protected $ref;

    /**
     * Constructeur.
     * @param string $ref
     */
    public function __construct($ref = null)
    {
        $this->ref = (string) $ref;
    }

    /**
     * Renvoie la ref.
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

    /**
     * Vérifie l'existence de l'unité
     *
     * @return bool
     */
    public function exists()
    {
        /** @var Container $container */
        $container = \Zend_Registry::get('container');
        /** @var UnitService $unitService */
        $unitService = $container->get(UnitService::class);

        try {
            $unitService->getUnit($this->ref);
        } catch (UnknownUnitException $e) {
            return false;
        }

        return true;
    }

    /**
     * Renvoi le référent textuel de l'API.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * getSymbol()
     * Va prendre en paramètre le référent textuel d'une unité et va retourner le symbol de l'unité
     * sous forme de chaîne de caractères. La variable booléenne $html si elle est à "true" permet de
     * transformer par exemple un exposant de la forme m^2 en m<sup>2</sup>.
     *
     * @return String
     */
    public function getSymbol()
    {
        /** @var Container $container */
        $container = \Zend_Registry::get('container');
        /** @var UnitService $unitService */
        $unitService = $container->get(UnitService::class);

        $unit = $unitService->getUnit($this->ref);

        return $unit->symbol;
    }


    /**
     * Vérifie qu'une ref est compatible avec l'unité.
     * @param string $ref
     * @return bool
     */
    public function isEquivalent($ref)
    {
        /** @var Container $container */
        $container = \Zend_Registry::get('container');
        /** @var OperationService $operationService */
        $operationService = $container->get(OperationService::class);

        return $operationService->areCompatible($this->ref, $ref);
    }

    /**
     * Cette méthode est utilisé soit pour récupérer le facteur de conversion :
     * -> d'une unité par rapport à son unité de référence ( param à null )
     * -> d'une unité par rapport à l'unité passé en paramètre ( dans le cas ou celles ci
     *     sont équivalentes)
     *
     * @param string $refUnit
     * @throws IncompatibleUnitsException
     * @return float Le facteur de conversion.
     */
    public function getConversionFactor($refUnit)
    {
        /** @var Container $container */
        $container = \Zend_Registry::get('container');
        /** @var OperationService $operationService */
        $operationService = $container->get(OperationService::class);

        return $operationService->getConversionFactor($this->ref, $refUnit);
    }

    /**
     * Sert à multiplier des unités. Renvoie une chaine de caractère qui
     *  correspond à une unité composéé d'unités de référence de grandeur
     *   physique de base (univoque).
     * @param array $components
     * @return UnitAPI $api
     */
    public static function multiply($components)
    {
        $unit = new ComposedUnit();
        $result = $unit->multiply($components);
        $api = new UnitAPI($result->getRef());
        return $api;
    }

    /**
     * Sert à ajouter des unités entre elles. Renvoi une unité composée
     *  d'unités de référence de grandeur physique de base.
     * @param array $components
     * @return UnitAPI
     */
    public static function calculateSum($components)
    {
        $unit = new ComposedUnit();
        $result = $unit->calculateSum($components);
        $api = new UnitAPI($result->getRef());
        return $api;
    }

    /**
     * Renvoie la liste des refs des unités compatibles, càd de même grandeur physique.
     * @return UnitAPI[]
     */
    public function getCompatibleUnits()
    {
        $unit = new ComposedUnit($this->getRef());

        return array_map(
            function (ComposedUnit $unit) {
                return new UnitAPI($unit->getRef());
            },
            $unit->getCompatibleUnits()
        );
    }


    /**
     * Renvoie l'unité normalisée associée à une unité.
     * @return UnitAPI
     */
    public function getNormalizedUnit()
    {
        $unit = new ComposedUnit($this->ref);
        return new UnitAPI($unit->getNormalizedUnit()->getRef());
    }

    /**
     * Retourne l'inverse de l'unité
     * @return UnitAPI
     */
    public function reverse()
    {
        $composedUnit = new ComposedUnit($this->getRef());
        $composedUnit->reverseUnit();
        return new UnitAPI($composedUnit->getRef());
    }

}
