<?php

namespace Unit;

use DI\Container;
use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\Operation\Addition;
use MyCLabs\UnitAPI\Operation\Multiplication;
use MyCLabs\UnitAPI\Operation\OperationComponent;
use MyCLabs\UnitAPI\Operation\Result\AdditionResult;
use MyCLabs\UnitAPI\Operation\Result\OperationResult;
use MyCLabs\UnitAPI\UnitOperationService;
use MyCLabs\UnitAPI\UnitService;

/**
 * API pour utiliser les unités.
 *
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class UnitAPI
{
    /**
     * Identifiant d'une unité
     * @var string
     */
    private $ref;

    /**
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
        /** @var UnitService $unitService */
        $unitService = \Core\ContainerSingleton::getContainer()->get(UnitService::class);
        $locale = \Core_Locale::loadDefault()->getId();

        try {
            $unitService->getUnit($this->ref, $locale);
        } catch (UnknownUnitException $e) {
            return false;
        }

        return true;
    }

    /**
     * Renvoi le référent textuel de l'API.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Va prendre en paramètre le référent textuel d'une unité et va retourner le symbol de l'unité
     * sous forme de chaîne de caractères. La variable booléenne $html si elle est à "true" permet de
     * transformer par exemple un exposant de la forme m^2 en m<sup>2</sup>.
     *
     * @todo À mettre en cache
     *
     * @throws UnknownUnitException
     * @return string
     */
    public function getSymbol()
    {
        /** @var UnitService $unitService */
        $unitService = \Core\ContainerSingleton::getContainer()->get(UnitService::class);
        $locale = \Core_Locale::loadDefault()->getId();

        $unit = $unitService->getUnit($this->ref, $locale);

        return $unit->symbol;
    }


    /**
     * Vérifie qu'une ref est compatible avec l'unité.
     *
     * @param string $ref
     *
     * @throws UnknownUnitException
     * @return bool
     */
    public function isEquivalent($ref)
    {
        if ($ref instanceof UnitAPI) {
            $ref = $ref->getRef();
        }

        /** @var UnitOperationService $operationService */
        $operationService = \Core\ContainerSingleton::getContainer()->get(UnitOperationService::class);

        return $operationService->areCompatible($this->ref, $ref);
    }

    /**
     * Cette méthode est utilisé soit pour récupérer le facteur de conversion :
     * -> d'une unité par rapport à son unité de référence ( param à null )
     * -> d'une unité par rapport à l'unité passé en paramètre ( dans le cas ou celles ci
     *     sont équivalentes)
     *
     * @param string $refUnit
     *
     * @throws UnknownUnitException
     * @throws IncompatibleUnitsException
     * @return float Le facteur de conversion.
     */
    public function getConversionFactor($refUnit)
    {
        /** @var UnitOperationService $operationService */
        $operationService = \Core\ContainerSingleton::getContainer()->get(UnitOperationService::class);

        return $operationService->getConversionFactor($this->ref, $refUnit);
    }

    /**
     * Sert à multiplier des unités. Renvoie une chaine de caractère qui
     *  correspond à une unité composéé d'unités de référence de grandeur
     *   physique de base (univoque).
     *
     * @param array $components
     *
     * @throws UnknownUnitException
     * @return OperationResult
     */
    public static function multiply($components)
    {
        /** @var UnitOperationService $operationService */
        $operationService = \Core\ContainerSingleton::getContainer()->get(UnitOperationService::class);

        $operation = new Multiplication();

        array_walk($components, function ($component) use ($operation) {
            $operation->addComponent(new OperationComponent($component['unit'], $component['signExponent']));
        });

        return $operationService->execute($operation);
    }

    /**
     * Sert à ajouter des unités entre elles. Renvoi une unité composée
     *  d'unités de référence de grandeur physique de base.
     *
     * @param array $components
     *
     * @throws UnknownUnitException
     * @return AdditionResult
     */
    public static function calculateSum($components)
    {
        /** @var UnitOperationService $operationService */
        $operationService = \Core\ContainerSingleton::getContainer()->get(UnitOperationService::class);

        $operation = new Addition();
        array_walk($components, function ($component) use ($operation) {
            $operation->addComponent(new OperationComponent($component, 1));
        });

        return $operationService->execute($operation);
    }

    /**
     * Renvoie la liste des refs des unités compatibles, càd de même grandeur physique.
     *
     * @throws UnknownUnitException
     * @return UnitAPI[]
     */
    public function getCompatibleUnits()
    {
        /** @var UnitService $unitService */
        $unitService = \Core\ContainerSingleton::getContainer()->get(UnitService::class);
        $locale = \Core_Locale::loadDefault()->getId();

        $unitDTOs = $unitService->getCompatibleUnits($this->ref, $locale);

        return array_map(function (UnitDTO $unitDTO) {
            return new UnitAPI($unitDTO->id);
        }, $unitDTOs);
    }


    /**
     * Renvoie l'unité normalisée associée à une unité.
     *
     * @throws UnknownUnitException
     * @return UnitAPI
     */
    public function getNormalizedUnit()
    {
        /** @var UnitService $unitService */
        $unitService = \Core\ContainerSingleton::getContainer()->get(UnitService::class);
        $locale = \Core_Locale::loadDefault()->getId();

        return new UnitAPI($unitService->getUnitOfReference($this->ref, $locale)->id);
    }

    /**
     * Retourne l'inverse de l'unité.
     *
     * @throws UnknownUnitException
     * @return UnitAPI
     */
    public function reverse()
    {
        /** @var UnitOperationService $operationService */
        $operationService = \Core\ContainerSingleton::getContainer()->get(UnitOperationService::class);

        return new UnitAPI($operationService->inverse($this->ref));
    }
}
