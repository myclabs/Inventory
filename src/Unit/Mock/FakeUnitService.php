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

        $km = new UnitDTO();
        $km->id = 'km';
        $km->label = 'kilometre';
        $km->symbol = 'km';

        $centkm = new UnitDTO();
        $centkm->id = '100km';
        $centkm->label = '100 kilometres';
        $centkm->symbol = '100km';

        $this->units = [
            'm'     => $m,
            'km'    => $km,
            '100km' => $centkm,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUnits($locale)
    {
        return array_values($this->units);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit($id, $locale)
    {
        if (isset($this->units[$id])) {
            return $this->units[$id];
        }

        // Cas particuliers d'unités composées utilisées dans les tests
        if ($id === 'g.j') {
            $unit = new UnitDTO();
            $unit->id = 'g.j';
            $unit->symbol = 'g.j';
            return $unit;
        }
        if ($id === 'kg.j') {
            $unit = new UnitDTO();
            $unit->id = 'kg.j';
            $unit->symbol = 'kg.j';
            return $unit;
        }
        if ($id === 'm^2.kg^2.s^-2') {
            $unit = new UnitDTO();
            $unit->id = 'm^2.kg^2.s^-2';
            $unit->symbol = 'm^2.kg^2.s^-2';
            return $unit;
        }

        throw UnknownUnitException::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitSystems($locale)
    {
        throw new \Exception("getUnitSystems not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function getPhysicalQuantities($locale)
    {
        throw new \Exception("getPhysicalQuantities not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function getCompatibleUnits($id, $locale)
    {
        switch ($id) {
            case 'm':
                return [];
        }

        throw UnknownUnitException::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfReference($id, $locale)
    {
        throw new \Exception("getUnitOfReference not implemented yet");
    }
}
