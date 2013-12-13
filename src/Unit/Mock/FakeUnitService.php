<?php

namespace Unit\Mock;

use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\UnitService;

/**
 * Mock pour UnitService pour les tests.
 */
class FakeUnitService implements UnitService
{
    private $units;

    public function __construct()
    {
        $m = new UnitDTO();
        $m->id = 'm';
        $m->label = 'metre';
        $m->symbol = 'm';

        $this->units = [
            'm' => $m,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUnits()
    {
        return array_values($this->units);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit($id)
    {
        if (isset($this->units[$id])) {
            return $this->units[$id];
        }

        throw UnknownUnitException::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitSystems()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * {@inheritdoc}
     */
    public function getPhysicalQuantities()
    {
        throw new \Exception("Not implemented");
    }
}
