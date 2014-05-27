<?php

namespace Unit\Mock;

use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\Operation\Operation;
use MyCLabs\UnitAPI\Operation\Result\AdditionResult;
use MyCLabs\UnitAPI\Operation\Result\MultiplicationResult;
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
        // Cas spéciaux utilisés dans les tests
        if ((string) $operation == 'g + kg') {
            return new AdditionResult('kg');
        }
        if ((string) $operation == 'kg + kg') {
            return new AdditionResult('kg');
        }
        if ((string) $operation == 't + g') {
            return new AdditionResult('kg');
        }
        if ((string) $operation == 'kg.j + g.j') {
            return new AdditionResult('m^2.kg^2.s^-2');
        }
        if ((string) $operation == 'j.animal * (g^2.animal)^-1') {
            return new MultiplicationResult('m^2.kg^-1.s^-2', 1000000.);
        }
        if ((string) $operation == 'j.animal * kg * (kg.m^2.s^-2.animal)^-1') {
            return new MultiplicationResult('kg', 1);
        }
        if ((string) $operation == 'g + kg + kg + g') {
            return new AdditionResult('kg');
        }
        if ((string) $operation == 'j.animal + animal.m^2.kg^1.s^-2') {
            return new AdditionResult('animal.m^2.kg.s^-2');
        }
        if ((string) $operation == 'g.animal + g^2.animal') {
            throw new IncompatibleUnitsException();
        }
        if ((string) $operation == 'gramme.animal + g^2.animal') {
            throw UnknownUnitException::create('gramme');
        }
        if ((string) $operation == 'j^2.animal^-1 * (t^2)^-1') {
            return new MultiplicationResult('m^4.animal^-1.s^-4', 0.000001);
        }

        throw new \Exception("Operation not implemented: $operation");
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
                        return 1000.;
                    case '100km':
                        return 100000.;
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
                        return 0.001;
                    case '100km':
                        return 100.;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            case 'g':
                switch ($unit2) {
                    case 'kg':
                        return 1000.;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            case 'kg':
                switch ($unit2) {
                    case 'kg':
                        return 1.;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            case 'kg.j':
                switch ($unit2) {
                    case 'm^2.kg^2.s^-2':
                        return 1;
                    default:
                        throw new IncompatibleUnitsException(
                            "$unit1 and $unit2 are incompatible, or conversion factor undefined?"
                        );
                }
                break;

            case 'g.j':
                switch ($unit2) {
                    case 'm^2.kg^2.s^-2':
                        return 1000.;
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
