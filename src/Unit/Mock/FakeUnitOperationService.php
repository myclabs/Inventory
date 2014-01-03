<?php

namespace Unit\Mock;

use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\Operation\Operation;
use MyCLabs\UnitAPI\UnitOperationService;
use MyCLabs\UnitAPI\UnitService;

/**
 * Mock pour UnitOperationService pour les tests.
 */
class FakeUnitOperationService implements UnitOperationService
{
    /**
     * @var UnitService
     */
    private $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Operation $operation)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * {@inheritdoc}
     */
    public function getConversionFactor($unit1, $unit2)
    {
        $this->unitService->getUnit($unit1, 'en');
        $this->unitService->getUnit($unit2, 'en');

        switch ($unit1) {

            case 'm':
                switch ($unit2) {
                    case 'm':
                        return 1;
                    case 'km':
                        return 0.001;
                    case '100km':
                        return 0.00001;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            case 'km':
                switch ($unit2) {
                    case 'km':
                        return 1;
                    case 'm':
                        return 1000;
                    case '100km':
                        return 0.01;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            default:
                throw new IncompatibleUnitsException(
                    "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function areCompatible($unit1, $unit2)
    {
        try {
            $this->getConversionFactor($unit1, $unit2);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function multiply($unit1, $unit2)
    {
        throw new \Exception("multiply not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function inverse($unit)
    {
        switch ($unit) {
            case 'm':
                return 'm^-1';
        }

        throw UnknownUnitException::create($unit);
    }
}
