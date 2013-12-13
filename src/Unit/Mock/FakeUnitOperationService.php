<?php

namespace Unit\Mock;

use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
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
    public function getConversionFactor($unit1, $unit2)
    {
        $this->unitService->getUnit($unit1);
        $this->unitService->getUnit($unit2);

        switch ($unit1) {

            case 'm':
                switch ($unit2) {
                    case 'm':
                        return 1;
                    default:
                        throw new IncompatibleUnitsException();
                }
                break;

            default:
                throw new IncompatibleUnitsException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function areCompatible($unit1, $unit2)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * {@inheritdoc}
     */
    public function multiply($unit1, $unit2)
    {
        throw new \Exception("Not implemented");
    }
}
